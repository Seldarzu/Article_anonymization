# Article Anonymization – Academic Paper Review System

A web-based academic paper review workflow that supports **PDF anonymization**, **reviewer assignment**, and **final decision PDF generation**.  
The system is designed for scenarios where author-identifying information must be hidden from reviewers while preserving the document structure.

---

## 🎯 Purpose

This project aims to support **double-blind / single-blind academic review workflows** by:
- Automatically anonymizing author-identifying information in submitted PDFs
- Managing editor (admin), reviewer, and author flows
- Generating final PDF outputs after the review process

---

## ✨ Features

- 📄 Article upload & management  
- 🕵️ PDF anonymization (configurable: author name, institution, email)  
- 👤 Role-based workflow:
  - **Admin / Editor**
  - **Reviewer (Hakem)**
  - **Author / User**
- 🧠 Python-assisted anonymization helpers (NLP / PDF processing)
- 🧾 Final PDF generation after evaluation
- 🌐 Web-based UI (PHP)

---

## 🧱 Tech Stack

| Layer            | Technology |
|------------------|------------|
| Backend / UI     | PHP |
| PDF / NLP Tools  | Python |
| Dependency Mgmt  | Composer |
| Frontend         | HTML / CSS / JavaScript |

---

## 📁 Project Structure

  Article_anonymization/
  │
  ├── admin/ # Admin / editor panel
  ├── hakem/ # Reviewer panel
  ├── user/ # Author / user pages
  │
  ├── includes/ # Shared PHP configs & helpers
  ├── scripts/ # Python scripts for PDF anonymization
  │
  ├── css/ # Stylesheets
  ├── js/ # JavaScript files
  │
  ├── final_dosyalar/ # Generated output PDFs (should be ignored in git)
  │
  ├── index.php # Entry point
  ├── composer.json # PHP dependencies
  └── README.md

yaml


---

## ⚙️ Setup (Local Development)

### 1️⃣ Requirements

- PHP **8.x**
- Composer
- Python **3.10+**
- Local server (Apache or PHP built-in server)

---

### 2️⃣ Install PHP Dependencies

```bash
composer install
3️⃣ Python Environment (If Anonymization Scripts Are Used)
bash
python -m venv venv
source venv/bin/activate   # Windows: venv\Scripts\activate
pip install -r requirements.txt
If requirements.txt does not exist yet, required packages should be documented based on the used scripts.

4️⃣ Configuration
Inside the includes/ directory, configure:

Database connection

Upload paths

Output paths

Anonymization options

⚠️ Important:
Do NOT commit files containing:

Database passwords

API keys

Absolute system paths

5️⃣ Run the Application
Using PHP built-in server:

bash
php -S localhost:8000
Open in browser:

arduino
http://localhost:8000
🔐 Security Notes
Uploaded files should be validated (PDF-only)

Generated files in final_dosyalar/ should not be committed

Sensitive configuration files must be excluded via .gitignore

🗂️ Recommended .gitignore
/vendor/
/venv/
/__pycache__/
/final_dosyalar/*
.env
*.log
🔄 Workflow Overview
Author uploads article

Admin configures anonymization rules

PDF anonymization is applied via Python scripts

Anonymized article is assigned to reviewers

Reviewer evaluates the article

Final PDF is generated and stored

🚧 Roadmap
 Docker support

 Automated tests

 Sample anonymized PDF

 CI pipeline

 Detailed anonymization rule documentation

📜 License
This project is released under the MIT License.
You are free to use, modify, and distribute it with attribution.

👩‍💻 Author
Arzu Selda Avcı
Computer Engineering
