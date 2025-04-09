from Crypto.Cipher import AES
from Crypto.Util.Padding import pad, unpad
import base64
import os
import sys
import json

encryption_key = b'This_is_a_32_byte_key_AES256__!!'

def encrypt_text(plain_text: str) -> str:
    iv = os.urandom(16)
    cipher = AES.new(encryption_key, AES.MODE_CBC, iv)
    encrypted_text = cipher.encrypt(pad(plain_text.encode('utf-8'), AES.block_size))
    return base64.b64encode(iv + encrypted_text).decode('utf-8')

def decrypt_text(encrypted_text: str) -> str:
    raw = base64.b64decode(encrypted_text)
    iv = raw[:16]
    cipher = AES.new(encryption_key, AES.MODE_CBC, iv)
    decrypted_text = unpad(cipher.decrypt(raw[16:]), AES.block_size)
    return decrypted_text.decode('utf-8')

if __name__ == "__main__":
    try:
        command = sys.argv[1]  
        text_list = json.loads(sys.argv[2])  

        if command == "encrypt":
            encrypted_list = [encrypt_text(text) for text in text_list]
            result = {"result": encrypted_list}
        elif command == "decrypt":
            decrypted_list = [decrypt_text(text) for text in text_list]
            result = {"result": decrypted_list}
        else:
            raise ValueError("Geçersiz komut: encrypt veya decrypt olmalı")

        print(json.dumps(result))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
