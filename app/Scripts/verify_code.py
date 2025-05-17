import sys
import os
from telethon.sync import TelegramClient

API_ID = 22584249
API_HASH = '5192e2d943fc80224078909f49625e42'


if len(sys.argv) < 3:
    print("Paramètres manquants")
    sys.exit(1)

phone = sys.argv[1]
code = sys.argv[2]
session_name = os.path.join(os.path.dirname(__file__), phone.replace("+", "").replace(" ", ""))


client = TelegramClient(session_name, API_ID, API_HASH)
client.connect()

if not client.is_user_authorized():
    try:
        # D'abord envoyer la demande de code pour obtenir le phone_code_hash
        code_request = client.send_code_request(phone)
        
        # Puis utiliser ce hash pour la vérification
        client.sign_in(
            phone=phone,
            code=code,
            phone_code_hash=code_request.phone_code_hash
        )
        print("Connexion réussie")
    except Exception as e:
        print(f"Erreur de connexion: {str(e)}")
        sys.exit(1)
else:
    print("Déjà connecté")

client.disconnect()
