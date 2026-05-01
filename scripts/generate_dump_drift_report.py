from __future__ import annotations
import json, re, zipfile, sys
from pathlib import Path
from datetime import datetime, timezone

base = Path(__file__).resolve().parents[1]
default_zip = Path('/mnt/data/wesl4494_db_saas.zip')
default_sql_name = 'wesl4494_db_saas.sql'
default_sql_path = Path('/mnt/data/work/db/wesl4494_db_saas.sql')
code_migrations = sorted(p.stem for p in (base/'database'/'migrations').glob('*.php'))

source = Path(sys.argv[1]) if len(sys.argv) > 1 else default_zip
sql_name = default_sql_name
if source.suffix.lower() == '.zip':
    with zipfile.ZipFile(source) as zf:
        if sql_name not in zf.namelist():
            sql_name = zf.namelist()[0]
        sql = zf.read(sql_name).decode('utf-8', 'ignore')
else:
    if not source.exists() and default_sql_path.exists():
        source = default_sql_path
    sql = source.read_text('utf-8', 'ignore')
    sql_name = source.name

create_tables = sorted(set(re.findall(r"CREATE TABLE `([^`]+)`", sql)))

applied = []
for m in re.finditer(r"INSERT INTO `migrations` .*? VALUES\s*(.*?);", sql, re.S):
    values = m.group(1)
    applied.extend(re.findall(r"\(\s*\d+\s*,\s*'([^']+)'\s*,\s*\d+\s*\)", values))
if not applied:
    applied = re.findall(r"\(\s*\d+\s*,\s*'([^']+)'\s*,\s*\d+\s*\)", sql)
applied = sorted(set(applied))

missing_in_dump = sorted(set(code_migrations) - set(applied))
missing_in_code = sorted(set(applied) - set(code_migrations))
critical_tables = [
    'migrations','estoques','stock_movements','pdv_offline_syncs','financial_audits','fiscal_documents','conta_recebers','conta_pagars'
]
critical_presence = {t: t in create_tables for t in critical_tables}

report = {
    'generated_at': datetime.now(timezone.utc).isoformat(),
    'source': str(source),
    'source_sql': sql_name,
    'migration_file_count': len(code_migrations),
    'applied_migration_count_in_dump': len(applied),
    'table_count_in_dump': len(create_tables),
    'migration_files_missing_in_dump': missing_in_dump,
    'applied_migrations_missing_in_code': missing_in_code,
    'critical_tables_in_dump': critical_presence,
}

(base/'docs'/'operacao').mkdir(parents=True, exist_ok=True)
(base/'docs'/'operacao'/'schema_drift_dump_report.json').write_text(json.dumps(report, indent=2, ensure_ascii=False))

lines = [
    '# Schema Drift Report (código x dump)',
    '',
    f"- Gerado em: {report['generated_at']}",
    f"- Fonte analisada: {source}",
    f"- SQL analisado: {sql_name}",
    f"- Migrations no código: {report['migration_file_count']}",
    f"- Migrations registradas no dump: {report['applied_migration_count_in_dump']}",
    f"- Tabelas detectadas no dump: {report['table_count_in_dump']}",
    '',
    '## Tabelas críticas',
]
for t, ok in critical_presence.items():
    lines.append(f"- {t}: {'presente' if ok else 'ausente'}")
lines += ['', '## Migrations do código ausentes no dump']
if missing_in_dump:
    lines += [f'- {m}' for m in missing_in_dump]
else:
    lines.append('- Nenhuma')
lines += ['', '## Migrations registradas no dump mas ausentes no código']
if missing_in_code:
    lines += [f'- {m}' for m in missing_in_code]
else:
    lines.append('- Nenhuma')
(base/'docs'/'operacao'/'schema_drift_dump_report.md').write_text('\n'.join(lines))
print('ok')
