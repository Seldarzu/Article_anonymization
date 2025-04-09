import fitz  
import cv2
import io
import sys
import numpy as np
from PIL import Image
import logging

def setup_logging():
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler('image_blur.log'),
            logging.StreamHandler()
        ]
    )
    return logging.getLogger(__name__)

logger = setup_logging()

def blur_faces(image_bytes):
    np_img = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(np_img, cv2.IMREAD_COLOR)
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    faces = face_cascade.detectMultiScale(img, scaleFactor=1.1, minNeighbors=5)

    for (x, y, w, h) in faces:
        face = img[y:y+h, x:x+w]
        img[y:y+h, x:x+w] = cv2.GaussianBlur(face, (99, 99), 30)

    _, buf = cv2.imencode(".png", img)
    return buf.tobytes()

def blur_faces_on_page(page, doc):
    for img in page.get_images(full=True):
        xref = img[0]
        base_img = doc.extract_image(xref)

        if "image" not in base_img:
            continue

        image_bytes = base_img["image"]
        rects = page.get_image_rects(xref)

        try:
            blurred_img = blur_faces(image_bytes)
            for rect in rects:
                page.insert_image(rect, stream=blurred_img)
        except Exception as e:
            logger.error(f"Görsel işleme hatası: {str(e)}")

def process_pdf(input_pdf, output_pdf):
    try:
        doc = fitz.open(input_pdf)

        for page in doc:
            blur_faces_on_page(page, doc)

        doc.save(output_pdf)
        doc.close()
        logger.info(f"PDF başarıyla kaydedildi: {output_pdf}")
        return True

    except Exception as e:
        logger.error(f"PDF işleme hatası: {str(e)}")
        return False

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Kullanım: python image_blur.py <girdi_pdf> <çıktı_pdf>")
        sys.exit(1)

    pdf_path = sys.argv[1]
    output_path = sys.argv[2]

    if process_pdf(pdf_path, output_path):
        print(f"[OK] Bulanıklaştırılmış PDF kaydedildi: {output_path}")
    else:
        print("[HATA] PDF kaydedilemedi")
