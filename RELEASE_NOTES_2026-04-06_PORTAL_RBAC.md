# Release Notes - Portal RBAC + correção do Dossiê RH

## Entregas desta versão
- Refatoração do portal do funcionário para RBAC por perfil.
- Cadastro de perfis do portal com permissões em JSON.
- Perfis padrão criados automaticamente:
  - Portal básico
  - Portal comercial
  - Portal completo
- Vínculo de perfil RBAC no acesso do funcionário ao portal.
- Permissões extras por funcionário para complementar o perfil.
- Consulta de produtos integrada ao modelo RBAC.
- Correção do link/menu **Dossiê RH** que gerava 404.
- Correção do controller do dossiê para usar a empresa da sessão quando `empresa_id` não vier na URL.

## Arquivos principais criados
- `app/Models/RHPortalPerfil.php`
- `app/Http/Controllers/RHPortalPerfilController.php`
- `resources/views/rh/portal_perfis/index.blade.php`
- `resources/views/rh/portal_perfis/form.blade.php`
- `database/migrations/2026_04_06_210000_create_rh_portal_perfis_table.php`
- `database/migrations/2026_04_06_211000_add_rbac_columns_to_rh_portal_funcionarios.php`

## Arquivos principais alterados
- `app/Models/RHPortalFuncionario.php`
- `app/Http/Controllers/FuncionarioController.php`
- `app/Http/Controllers/RHPortalFuncionarioController.php`
- `app/Http/Controllers/RHPortalAcessoController.php`
- `app/Http/Controllers/RHDossieController.php`
- `resources/views/funcionarios/index.blade.php`
- `resources/views/funcionarios/show.blade.php`
- `resources/views/rh/portal_funcionario/index_externo.blade.php`
- `routes/web.php`
- `routes/web1.php`
- `routes/web2.php`
- `routes/legacy/web1.php`
- `routes/legacy/web2.php`

## Banco de dados
Executar:
```bash
php artisan migrate
```

## Observação de compatibilidade
- A coluna legada `pode_ver_relatorio_produtos` foi mantida para compatibilidade.
- Registros antigos de portal são migrados automaticamente para um perfil padrão na migration.
