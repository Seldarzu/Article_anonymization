import spacy
import logging
import re
from encryption_functions import encrypt_text

def setup_logging():
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler('nlp_functions.log'),
            logging.StreamHandler()
        ]
    )
    return logging.getLogger(__name__)

logger = setup_logging()

def load_spacy_model():
    try:
        nlp = spacy.load("en_core_web_sm")
        logger.info("SpaCy model başarıyla yüklendi")
        return nlp
    except OSError:
        logger.warning("Model yüklü değil, indiriliyor...")
        from spacy.cli import download
        download("en_core_web_sm")
        return spacy.load("en_core_web_sm")

def extract_keywords(text):
    nlp = load_spacy_model()
    doc = nlp(text)

    keywords = []
    for token in doc:
        if not token.is_stop and not token.is_punct and len(token.text) > 2:
            keywords.append(token.text.lower())

    keywords = list(set(keywords))
    logger.info(f"Anahtar kelimeler tespit edildi: {keywords}")
    return keywords

def anonymize_text(text, options):
    nlp = load_spacy_model()
    doc = nlp(text)
    anonymized_text = text

    if 'isim' in options:
        for ent in doc.ents:
            if ent.label_ == 'PERSON':
                encrypted_name = encrypt_text(ent.text)
                anonymized_text = anonymized_text.replace(ent.text, encrypted_name)

    if 'kurum' in options:
        for ent in doc.ents:
            if ent.label_ == 'ORG':
                encrypted_org = encrypt_text(ent.text)
                anonymized_text = anonymized_text.replace(ent.text, encrypted_org)

    if 'eposta' in options:
        emails = re.findall(r'\b[\w\.-]+@[\w\.-]+\.\w+\b', text)
        for email in emails:
            encrypted_email = encrypt_text(email)
            anonymized_text = anonymized_text.replace(email, encrypted_email)

    logger.info("Metin başarıyla anonimleştirildi")
    return anonymized_text
