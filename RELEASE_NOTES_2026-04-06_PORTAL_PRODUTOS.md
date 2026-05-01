# Alterações - Portal do Funcionário (consulta de produtos)

## O que foi adicionado
- Permissão por funcionário no portal externo para liberar a consulta de produtos.
- Nova tela no portal do funcionário com listagem somente leitura de produtos.
- Filtro por ID ou nome do produto.
- Configuração administrativa na ficha do funcionário para:
  - ativar/desativar o portal
  - liberar/bloquear a consulta de produtos

## Arquivos principais alterados
- `app/Models/RHPortalFuncionario.php`
- `app/Http/Controllers/FuncionarioController.php`
- `app/Http/Controllers/RHPortalAcessoController.php`
- `app/Http/Controllers/RHPortalFuncionarioController.php`
- `resources/views/funcionarios/show.blade.php`
- `resources/views/rh/portal_funcionario/index_externo.blade.php`
- `resources/views/rh/portal_funcionario/produtos_externo.blade.php`
- `routes/web.php`
- `routes/web1.php`
- `routes/web2.php`
- `routes/legacy/web1.php`
- `routes/legacy/web2.php`
- `database/migrations/2026_04_06_190000_add_produtos_permission_to_rh_portal_funcionarios.php`

## Banco de dados
Executar as migrations para criar a coluna nova na tabela `rh_portal_funcionarios`:
- `pode_ver_relatorio_produtos`

Comando sugerido:
```bash
php artisan migrate
```
