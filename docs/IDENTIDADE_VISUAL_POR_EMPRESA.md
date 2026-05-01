# Identidade visual por empresa

Esta entrega adiciona:

- upload da logo da empresa no menu **Configurações > Identidade visual**
- upload da imagem de fundo do login
- persistência no banco (`empresas.branding_logo_path` e `empresas.branding_background_path`)
- arquivos salvos no `storage/app/public/empresas/{id}/branding`
- atualização automática no layout principal e nas telas de login

## Passos após subir

1. `php artisan migrate`
2. garantir o link do storage: `php artisan storage:link`
3. limpar cache: `php artisan optimize:clear`

## Uso no login

O login usa automaticamente a empresa logada e também aceita contexto por `?empresa={id-ou-hash}`.
