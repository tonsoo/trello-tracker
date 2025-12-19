# Instalação

1. Requerer o pacote:
```bash
composer require tonso/trello-tracker
```

2. Publicar a configuração:
```bash
php artisan vendor:publish --tag=trello-tracker-config
```

3. Configurar o `.env` (veja [`configuration.md`](configuration.md)).

4. Garantir a fila configurada e um worker em execução:
```bash
php artisan queue:work
```

5. Configurar os webhooks do WhatsApp no app da Meta (veja [`whatsapp-setup.md`](whatsapp-setup.md)).
