import sys
import os
import json
import fitz  
import logging

from pdf_anonymize import detect_authors, save_anonymized_pdf
from image_blur import process_pdf
from nlp_functions import extract_keywords, anonymize_text
from encryption_functions import encrypt_text  

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[logging.StreamHandler()]
)
logger = logging.getLogger(__name__)

def prompt_admin_choices():
    print("Anonimleştirme seçeneklerini giriniz (virgülle ayrılmış):")
    print("Örnek: isim, kurum, eposta")
    choices = input("Seçiminiz: ")
    options = [c.strip().lower() for c in choices.split(',') if c.strip()]
    return options

def extract_page_keywords(pdf_path):
    try:
        doc = fitz.open(pdf_path)
        first_page_text = doc[0].get_text()
        doc.close()
        keywords = extract_keywords(first_page_text)
        logger.info(f"Anahtar kelimeler: {keywords}")
        return keywords
    except Exception as e:
        logger.error(f"Anahtar kelime çıkarım hatası: {e}")
        return []

def main():
    if len(sys.argv) < 3:
        print("Kullanım: integrated_anonymization.py <girdi_pdf> <çıktı_pdf> [admin_options]")
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf_name = os.path.basename(sys.argv[2])
    
    
    base_dir = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))
    output_folder = os.path.join(base_dir, "anonim_icerikler")
    os.makedirs(output_folder, exist_ok=True)
    output_pdf = os.path.join(output_folder, output_pdf_name)

    if not os.path.exists(input_pdf):
        print(f"Dosya bulunamadı: {input_pdf}")
        sys.exit(1)

    keywords = extract_page_keywords(input_pdf)
    logger.info("Çıkarılan anahtar kelimeler: " + str(keywords))
    
    try:
        detected_authors = detect_authors(input_pdf)
        logger.info("Tespit edilen yazar/iletişim bilgileri: " + str(detected_authors))
    except Exception as e:
        logger.error("Yazar tespitinde hata oluştu: " + str(e))
        sys.exit(1)
    
    encrypted_info = {info: encrypt_text(info) for info in detected_authors}
    encryption_details = {
        "detected_authors": detected_authors,
        "encrypted_info": encrypted_info
    }
    encryption_json_path = os.path.join(output_folder, "encryption_details.json")
    with open(encryption_json_path, "w", encoding="utf-8") as f:
        json.dump(encryption_details, f, ensure_ascii=False, indent=4)
    logger.info("Şifreleme detayları JSON olarak kaydedildi: " + encryption_json_path)
    
    if len(sys.argv) >= 4 and sys.argv[3].strip():
        options_arg = sys.argv[3]
        options = [x.strip().lower() for x in options_arg.split(',') if x.strip()]
    else:
        if sys.stdin.isatty():
            options = prompt_admin_choices()
        else:
            options = ["isim", "kurum", "eposta"]
    logger.info("Anonimleştirme seçenekleri: " + str(options))
    
    temp_anonymized_pdf = "temp_anonymized.pdf"
    if not save_anonymized_pdf(input_pdf, temp_anonymized_pdf, detected_authors):
        logger.error("Metin anonimleştirme işlemi başarısız.")
        sys.exit(1)
    else:
        logger.info("Metin anonimleştirme tamamlandı.")
    
    try:
        doc = fitz.open(temp_anonymized_pdf)
        for page in doc:
            page_text = page.get_text()
            lower_text = page_text.lower()
            if any(sec in lower_text for sec in ["introduction", "related work", "references", "acknowledgments"]):
                logger.info("Bu sayfa kritik bölüm içeriyor, anonimleştirmeye dahil edilmiyor.")
                continue
            anonymized_text = anonymize_text(page_text, options)
            logger.info("Sayfa metni anonimleştirildi (ilk 200 karakter):")
            logger.info(anonymized_text[:200])
        doc.close()
    except Exception as e:
        logger.error("Ek metin anonimleştirme hatası: " + str(e))
    
    if not process_pdf(temp_anonymized_pdf, output_pdf):
        logger.error("Görsel işleme (bulanıklaştırma) işlemi başarısız.")
        sys.exit(1)
    else:
        logger.info("Görsel işleme tamamlandı.")
    
    logger.info("Tüm anonimleştirme işlemleri tamamlandı.")
    logger.info("Anonimleştirilmiş PDF dosyası: " + output_pdf)
    
    
    final_details = {
        "status": "success",
        "output_pdf": "anonim_icerikler/" + output_pdf_name,
        "encryption_json": "anonim_icerikler/encryption_details.json",
        "encrypted_info": encrypted_info,
        "extracted_keywords": keywords,
        "admin_options": options
    }
    final_json_path = os.path.join(output_folder, "final_details.json")
    with open(final_json_path, "w", encoding="utf-8") as f:
        json.dump(final_details, f, ensure_ascii=False, indent=4)
    logger.info("Son detaylar JSON olarak kaydedildi: " + final_json_path)
    
    
    print(json.dumps(final_details, ensure_ascii=False))

if __name__ == "__main__":
    main()
