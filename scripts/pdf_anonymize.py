import sys
import os
import json
import fitz  
import logging


def setup_logging():
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[logging.StreamHandler()]
    )
    return logging.getLogger(__name__)

logger = setup_logging()

def load_spacy_model():
    try:
        import spacy
        nlp = spacy.load("en_core_web_sm")
        logger.info("SpaCy model başarıyla yüklendi")
        return nlp
    except OSError:
        logger.warning("Model yüklü değil, indiriliyor...")
        from spacy.cli import download
        download("en_core_web_sm")
        import spacy
        return spacy.load("en_core_web_sm")

nlp = load_spacy_model()

def detect_authors(pdf_path):
    try:
        logger.info(f"PDF analizi başlatıldı: {pdf_path}")
        authors = []
        with fitz.open(pdf_path) as doc:
            first_page = doc[0]
            blocks = first_page.get_text("dict")['blocks']
            title_found = False
            for block in blocks:
                block_text = " ".join(span["text"] for line in block.get("lines", []) 
                                      for span in line.get("spans", []))
                if not title_found and len(block_text.split()) > 3:
                    max_font = max(span["size"] for line in block.get("lines", []) 
                                   for span in line.get("spans", []))
                    if max_font > 11:
                        title_found = True
                        continue
                if title_found:
                    
                    doc_spacy = nlp(block_text)
                    authors.extend(
                        ent.text for ent in doc_spacy.ents 
                        if ent.label_ == "PERSON" and len(ent.text.split()) >= 2
                    )
                    import re
                    emails = re.findall(r'\b[\w\.-]+@[\w\.-]+\.\w+\b', block_text)
                    if emails:
                        authors.extend(emails)
        logger.info(f"{len(authors)} yazar/adres tespit edildi")
        return list(set(authors))
    except Exception as e:
        logger.error(f"Yazar tespit hatası: {str(e)}")
        raise

def save_anonymized_pdf(input_path, output_path, authors):
    try:
        doc = fitz.open(input_path)
        for page in doc:
            for author in authors:
                if isinstance(author, str):
                    text_instances = page.search_for(author)
                    for inst in text_instances:
                        page.add_redact_annot(inst, "[ANONYMIZED]")
            page.apply_redactions()
        doc.save(output_path)
        doc.close()
        return True
    except Exception as e:
        logger.error(f"PDF kaydetme hatası: {str(e)}")
        return False


if __name__ == "__main__":
    if len(sys.argv) != 3:
        print(json.dumps({"error": "Kullanım: pdf_anonymize.py <input> <output>"}))
        
    
    try:
        authors = detect_authors(sys.argv[1])
        success = save_anonymized_pdf(sys.argv[1],sys.argv[2], authors)
        if success:
            print(json.dumps({
                "status":"success",
                "output_file":sys.argv[2],
                "authors":authors
            }))
        else:
            print(json.dumps({"error": "PDF kaydedilemedi"}))
    except Exception as e:
        logger.error(f"Hata: {e}")
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
