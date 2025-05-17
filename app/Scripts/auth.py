from telethon.sync import TelegramClient

API_ID=22584249
API_HASH='5192e2d943fc80224078909f49625e42'
phone = '+2250151995872'
session_name = 'scraper-session'

client = TelegramClient(session_name, API_ID, API_HASH)
client.start(phone=phone)
print("Session Telegram sauvegardée.")

