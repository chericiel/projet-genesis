<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Code de vérification - Genesis</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
        <div style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="color: #007bff;">👋 Bienvenue sur Genesis</h2>
        <p style="font-size: 16px;">Voici votre code de connexion :</p>
        <div style="background-color: #f1f3f5; padding: 15px; text-align: center; font-size: 28px; letter-spacing: 4px; font-weight: bold; color: #343a40;">
            {{ $code }}
        </div>
        <p style="margin-top: 20px; font-size: 14px; color: #6c757d;">
            Ce code est valable pendant 10 minutes. Ne le partagez avec personne.
        </p>
        <p style="font-size: 14px;">— L'équipe <strong>Genesis</strong></p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="http://localhost:3000/verify-code" style="display: inline-block; background-color: #007bff; color: #fff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: bold;">
            Valider mon code
        </a>
    </div>

</body>
</html>
