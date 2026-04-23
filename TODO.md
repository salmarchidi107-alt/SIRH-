# Fix Chatbot "⚠️ Impossible de contacter le serveur"

## Problèmes identifiés
1. Route `/ask-ai` absente de `routes/web.php` → 404
2. `SalaryTool` non instancié/non géré dans `AssistantRH.php`
3. Chatbot dupliqué + chat WhatsApp inline obsolète dans `layouts/app.blade.php`

## Étapes

- [x] 1. Créer TODO.md
- [ ] 2. Ajouter route `/ask-ai` dans `routes/web.php`
- [ ] 3. Ajouter `SalaryTool` dans `app/Ai/Agents/AssistantRH.php`
- [ ] 4. Nettoyer `resources/views/layouts/app.blade.php`
- [ ] 5. Vider le cache des routes
