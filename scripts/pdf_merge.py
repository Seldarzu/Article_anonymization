import sys
import fitz  

if len(sys.argv) != 4:
    print("Kullanım: merge_pdfs.py <pdf1> <pdf2> <output>")
    sys.exit(1)

pdf1_path, pdf2_path, output_path = sys.argv[1], sys.argv[2], sys.argv[3]

doc = fitz.open()
doc1 = fitz.open(pdf1_path)
doc2 = fitz.open(pdf2_path)

doc.insert_pdf(doc1)
doc.insert_pdf(doc2)

doc.save(output_path)
doc.close()
