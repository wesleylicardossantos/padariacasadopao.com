# Publicação Hostgator / cPanel

- Permissão de escrita em `storage/` e `bootstrap/cache/`.
- Se o domínio não apontar para `/public`, use o `index.php` da raiz deste pacote.
- Rode as migrations/SQL pendentes de RH e PDV offline.
- O wildcard do subdomínio também precisa apontar para a pasta correta do projeto para eliminar `AH01276`.
