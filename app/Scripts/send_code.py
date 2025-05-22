import sys
import os
from telethon.sync import TelegramClient

API_ID = 22584249
API_HASH = '5192e2d943fc80224078909f49625e42'

if len(sys.argv) < 2:
    print("Numéro manquant")
    sys.exit(1)

phone = sys.argv[1]
session_name = os.path.join(os.path.dirname(__file__), phone.replace("+", "").replace(" ", ""))

client = TelegramClient(session_name, API_ID, API_HASH)
client.connect()

if not client.is_user_authorized():
    try:
        code_request = client.send_code_request(phone)
        print(code_request.phone_code_hash)  # Retourne le hash vers Laravel
    except Exception as e:
        print(f"Erreur d'envoi du code: {str(e)}")
        sys.exit(1)
else:
    print("Déjà connecté")

client.disconnect()
