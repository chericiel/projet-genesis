<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de mot de passe</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="max-width: 500px; background-color: #fff; margin: auto; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="color: #333;">Réinitialisation de mot de passe</h2>
        <p style="font-size: 16px;">Voici votre code de sécurité :</p>
        <p style="font-size: 30px; font-weight: bold; color: #4caf50;">{{ $code }}</p>
        <p style="font-size: 14px; color: #777;">Ce code expire dans 10 minutes. Si vous n'avez pas demandé de réinitialisation, ignorez ce message.</p>
        <hr>
        <p style="font-size: 12px; color: #aaa;">© 2025 - Projet Genesis</p>
    </div>
</body>
</html>
