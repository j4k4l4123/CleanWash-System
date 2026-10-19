import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    tcPr.append(parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>'))

def set_cell_margins(cell, top=120, bottom=120, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def set_cell_borders(cell, top="D3D3D3", bottom="D3D3D3", left="none", right="none", sz="4"):
    tcPr = cell._tc.get_or_add_tcPr()
    borders_xml = f'<w:tcBorders {nsdecls("w")}>'
    borders_xml += f'<w:top w:val="single" w:sz="{sz}" w:space="0" w:color="{top}"/>' if top != "none" else '<w:top w:val="none"/>'
    borders_xml += f'<w:bottom w:val="single" w:sz="{sz}" w:space="0" w:color="{bottom}"/>' if bottom != "none" else '<w:bottom w:val="none"/>'
    borders_xml += f'<w:left w:val="single" w:sz="{sz}" w:space="0" w:color="{left}"/>' if left != "none" else '<w:left w:val="none"/>'
    borders_xml += f'<w:right w:val="single" w:sz="{sz}" w:space="0" w:color="{right}"/>' if right != "none" else '<w:right w:val="none"/>'
    borders_xml += '</w:tcBorders>'
    tcPr.append(parse_xml(borders_xml))

def create_document():
    doc = docx.Document()

    # Set page margins (Normal 1 inch = 0.8 in for compact 5-6 page fit)
    for section in doc.sections:
        section.top_margin = Inches(0.75)
        section.bottom_margin = Inches(0.75)
        section.left_margin = Inches(0.75)
        section.right_margin = Inches(0.75)

    # Styles
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Calibri'
    normal_style.font.size = Pt(10.5)
    normal_style.font.color.rgb = RGBColor(0x1F, 0x29, 0x37) # Slate 800
    normal_style.paragraph_format.line_spacing = 1.15
    normal_style.paragraph_format.space_after = Pt(4)

    # --- HEADER / TITLE BLOCK ---
    title_p = doc.add_paragraph()
    title_p.paragraph_format.space_before = Pt(0)
    title_p.paragraph_format.space_after = Pt(2)
    title_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_sub = title_p.add_run("LEMBAR KERJA MAHASISWA (LKM) MINGGU 6\n")
    r_sub.font.size = Pt(11)
    r_sub.font.bold = True
    r_sub.font.color.rgb = RGBColor(0x4B, 0x55, 0x63)

    r_title = title_p.add_run("INTEGRASI QA & QC PADA MODEL SDLC")
    r_title.font.size = Pt(16)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(0x1E, 0x3A, 0x8A) # Deep Blue

    sub_p = doc.add_paragraph()
    sub_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    sub_p.paragraph_format.space_after = Pt(10)
    r_cpmk = sub_p.add_run("Mata Kuliah: Assurance Quality & Quality Control | Sub-CPMK: 5 & 6\nMenyesuaikan Matakuliah Proyek Inovasi Digital (Dosen Pengampu: Bapak Rohim)")
    r_cpmk.font.size = Pt(9.5)
    r_cpmk.font.italic = True
    r_cpmk.font.color.rgb = RGBColor(0x4B, 0x55, 0x63)

    # Metadata Card Table
    meta_table = doc.add_table(rows=5, cols=2)
    meta_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    meta_table.autofit = False

    meta_data = [
        ("Kelompok", "Kelompok 2"),
        ("Anggota Kelompok", "1. Adi\n2. Alvin Dwi Lintang H.\n3. Faqih\n4. Yasir\n5. Erna\n6. Naila"),
        ("Studi Kasus Proyek", "Sistem Manajemen Laundry (Laundry Management System) Berbasis Web"),
        ("Model SDLC Terpilih", "Agile Software Development (Scrum Framework - 3 Iterative Sprints)"),
        ("Tech Stack & Lingkungan", "Laravel 12 (PHP 8.4), Blade, Tailwind CSS, MySQL, PHPUnit, Laravel Pint")
    ]

    col_widths = [Inches(2.0), Inches(5.0)]
    for row_idx, (k, v) in enumerate(meta_data):
        row = meta_table.rows[row_idx]
        c0, c1 = row.cells[0], row.cells[1]
        c0.width = col_widths[0]
        c1.width = col_widths[1]

        p0 = c0.paragraphs[0]
        p0.paragraph_format.space_after = Pt(2)
        r0 = p0.add_run(k)
        r0.font.bold = True
        r0.font.size = Pt(9.5)
        r0.font.color.rgb = RGBColor(0x1E, 0x3A, 0x8A)

        p1 = c1.paragraphs[0]
        p1.paragraph_format.space_after = Pt(2)
        r1 = p1.add_run(v)
        r1.font.size = Pt(9.5)

        set_cell_background(c0, "F0F4F8")
        set_cell_background(c1, "FFFFFF")
        set_cell_margins(c0, top=60, bottom=60, left=100, right=100)
        set_cell_margins(c1, top=60, bottom=60, left=100, right=100)
        set_cell_borders(c0, top="CBD5E1", bottom="CBD5E1", left="CBD5E1", right="CBD5E1", sz="4")
        set_cell_borders(c1, top="CBD5E1", bottom="CBD5E1", left="CBD5E1", right="CBD5E1", sz="4")

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # Helper function for Section Headers
    def add_section_header(title, subtitle=None):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.keep_with_next = True
        r = p.add_run(title)
        r.font.size = Pt(13)
        r.font.bold = True
        r.font.color.rgb = RGBColor(0x0F, 0x2A, 0x5C)

        if subtitle:
            p2 = doc.add_paragraph()
            p2.paragraph_format.space_before = Pt(0)
            p2.paragraph_format.space_after = Pt(4)
            p2.paragraph_format.keep_with_next = True
            r2 = p2.add_run(subtitle)
            r2.font.size = Pt(9.5)
            r2.font.italic = True
            r2.font.color.rgb = RGBColor(0x64, 0x74, 0x8B)

    def add_sub_header(title):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(8)
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.keep_with_next = True
        r = p.add_run(title)
        r.font.size = Pt(11)
        r.font.bold = True
        r.font.color.rgb = RGBColor(0x1E, 0x40, 0xAF)

    # =========================================================================
    # BAGIAN A: PEMAHAMAN SDLC (Sub-CPMK 5)
    # =========================================================================
    add_section_header("BAGIAN A: PEMAHAMAN SDLC (Sub-CPMK 5)")

    add_sub_header("1. Alur Model SDLC pada Proyek Sistem Manajemen Laundry")
    p_a1 = doc.add_paragraph()
    p_a1.add_run(
        "Untuk pengembangan Sistem Manajemen Laundry Kelompok 2, model SDLC yang diterapkan adalah "
    )
    r_bold = p_a1.add_run("Agile Software Development dengan kerangka kerja Scrum (Iterative Sprints)")
    r_bold.font.bold = True
    p_a1.add_run(
        ". Pilihan model ini tercermin secara eksplisit pada jadwal kerja proyek (Gantt Chart kelompok), "
        "di mana proses pengerjaan dibagi menjadi tiga siklus sprint iteratif dengan tonggak pengujian sistem di setiap akhir sprint, "
        "mulai dari analisis kebutuhan hingga pemeliharaan."
    )

    # Embed Diagram
    diagram_path = "Tugas/diagram_sdlc_agile_laundry.png"
    if os.path.exists(diagram_path):
        p_img = doc.add_paragraph()
        p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img.paragraph_format.space_before = Pt(6)
        p_img.paragraph_format.space_after = Pt(2)
        run_img = p_img.add_run()
        run_img.add_picture(diagram_path, width=Inches(6.8))

        p_caption = doc.add_paragraph()
        p_caption.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_caption.paragraph_format.space_after = Pt(6)
        r_cap = p_caption.add_run("Gambar 1. Alur Integrasi SDLC Agile (Scrum) pada Sistem Manajemen Laundry Kelompok 2")
        r_cap.font.size = Pt(8.5)
        r_cap.font.italic = True
        r_cap.font.color.rgb = RGBColor(0x4B, 0x55, 0x63)

    p_a1_detail = doc.add_paragraph()
    p_a1_detail.add_run("Rincian tahapan alur SDLC proyek sesuai jadwal nyata Gantt Chart:\n")

    steps = [
        ("a. Requirement Analysis & Product Backlog",
         "Tim melakukan wawancara dengan pemilik laundry untuk menggali kebutuhan operasional. Hasil analisis didekomposisi menjadi Product Backlog berisi user stories utama: autentikasi kasir/admin, pencatatan orderan kiloan/satuan, master jenis layanan, pencatatan transaksi pembayaran, cetak nota, laporan keuangan, dan pelacakan status cucian oleh konsumen."),

        ("b. Sprint Planning & Penjadwalan",
         "Membagi backlog ke dalam 3 milestone Sprint dengan estimasi waktu dan alokasi kerja untuk 6 anggota (Adi, Alvin, Faqih, Yasir, Erna, Naila) secara hybrid (kolaborasi online via GitHub & tatap muka offline untuk sinkronisasi logika)."),

        ("c. Iterasi Pengembangan (3 Sprints Berjalan):",
         "• Sprint 1 (22 Sep - 05 Okt 2026, Durasi 14 Hari): Pembuatan modul Login & Logout multi-role serta pengelolaan data Orderan & Pelanggan. Diakhiri dengan Pengujian Sistem Sprint 1 pada 05 Oktober 2026.\n"
         "• Sprint 2 (07 Okt - 12 Okt 2026, Durasi 6 Hari): Pembuatan fitur Mengelola Jenis Layanan & Petugas, Status Orderan & Sistem Pembayaran, serta Dashboard ringkasan bisnis. Diakhiri dengan Pengujian Sistem Sprint 2 pada 12 Oktober 2026.\n"
         "• Sprint 3 (13 Okt - 19 Okt 2026, Durasi 7 Hari): Pembuatan fitur Cetak Nota kasir, Laporan Keuangan pendapatan, dan Halaman Pelacakan Status Cucian oleh Pelanggan secara mandiri. Diakhiri dengan Pengujian Sistem Komprehensif pada 16 - 19 Oktober 2026."),

        ("d. Testing, Sprint Review & Retrospective",
         "Pada akhir tiap iterasi, dilakukan verifikasi kualitas internal (Quality Control) dan demonstrasi fungsional fitur. Feedback digunakan untuk menyempurnakan sprint berikutnya."),

        ("e. Deployment & Maintenance",
         "Aplikasi di-deploy ke cloud environment (Vercel & Staging Server Docker) dengan database MySQL. Tahap pemeliharaan mencakup monitoring log error, perbaikan defect (bug fixes), serta optimasi query basis data secara berkala.")
    ]

    for title, desc in steps:
        p_step = doc.add_paragraph()
        p_step.paragraph_format.left_indent = Inches(0.2)
        p_step.paragraph_format.space_after = Pt(3)
        r_st = p_step.add_run(f"{title}: ")
        r_st.font.bold = True
        p_step.add_run(desc)

    # Soal 2
    add_sub_header("2. Alasan Kesesuaian Model Agile dan Satu Kelemahannya")
    p_a2 = doc.add_paragraph()
    p_a2.add_run("Model Agile (Scrum) sangat sesuai untuk pengembangan Sistem Manajemen Laundry karena alasan-alasan berikut:\n")

    reasons = [
        ("Dekomposisi Fitur Modular dan Inkremental",
         "Aplikasi laundry memiliki modul-modul bisnis yang saling independen dan dapat diselesaikan bertahap. Fitur inti kasir (order & pelanggan) dapat langsung difungsikan di Sprint 1, kemudian diperkaya dengan modul pembayaran & dashboard di Sprint 2, dan disempurnakan dengan reporting serta public tracking di Sprint 3."),

        ("Fleksibilitas Terhadap Penyesuaian Kebutuhan Pengguna",
         "Alur kerja operasional laundry di lapangan seringkali memerlukan penyesuaian (misal perubahan status pesanan: 'Baru -> Cuci -> Kering -> Setrika -> Siap Diambil', format nota thermal, atau validasi pembayaran). Agile memungkinkan tim mengintegrasikan perubahan tanpa merombak arsitektur sistem dari awal."),

        ("Manajemen Risiko dan Kualitas Terjadwal Dini",
         "Sebagaimana tertera pada Gantt Chart, tonggak pengujian sistem diadakan di akhir setiap sprint (Row 5, 9, dan 13). Ini mencegah terjadinya akumulasi cacat (defect accumulation) di akhir proyek."),

        ("Karakteristik Kolaborasi Tim Mahasiswa",
         "Dengan 6 orang pengembang dalam skema kerja hybrid (online/offline), rentang sprint 1-2 minggu memberikan ritme kerja (cadence) yang terukur, akuntabilitas personal yang jelas, dan evaluasi berkala.")
    ]

    for r_title, r_desc in reasons:
        p_r = doc.add_paragraph()
        p_r.paragraph_format.left_indent = Inches(0.2)
        p_r.paragraph_format.space_after = Pt(2)
        r_bold = p_r.add_run(f"• {r_title}: ")
        r_bold.font.bold = True
        p_r.add_run(r_desc)

    p_weakness = doc.add_paragraph()
    p_weakness.paragraph_format.space_before = Pt(3)
    r_w_title = p_weakness.add_run("Satu Kelemahan Utama Model Agile pada Proyek Ini:\n")
    r_w_title.font.bold = True
    r_w_title.font.color.rgb = RGBColor(0x99, 0x1B, 0x1B) # Red
    p_weakness.add_run(
        "Kelemahan paling nyata adalah kecenderungan dokumentasi teknis yang minim atau tertinggal (Lack of Comprehensive Documentation). "
        "Karena manifesto Agile memprioritaskan 'Working Software over Comprehensive Documentation' dan tim didorong bergerak cepat memenuhi batas waktu sprint, "
        "dokumentasi arsitektur database, kamus data, spesifikasi API endpoint, dan changelog berisiko tidak terbarui dengan disiplin. "
        "Hal ini dapat menyulitkan proses maintenance atau serah terima sistem di masa mendatang.\n"
        "Mitigasi Kelompok: Kelompok 2 menetapkan Quality Assurance berupa checklist 'Definition of Done (DoD)', "
        "di mana setiap fitur baru wajib menyertakan dokumentasi inline PHPDoc dan pembaruan migrasi database sebelum disetujui untuk di-merge."
    )

    # Soal 3
    add_sub_header("3. Perbandingan Model Agile dengan Model Waterfall")
    p_a3 = doc.add_paragraph()
    p_a3.add_run(
        "Berikut adalah perbandingan mendalam antara model Agile (Scrum) yang diterapkan pada Sistem Laundry "
        "dengan model Waterfall konvensional berdasarkan karakteristik arsitektural dan keunggulannya:"
    )

    compare_table = doc.add_table(rows=8, cols=3)
    compare_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    compare_table.autofit = False

    comp_widths = [Inches(1.8), Inches(2.6), Inches(2.6)]
    comp_headers = ["Dimensi Perbandingan", "Model Agile (Scrum) - Proyek Laundry", "Model Waterfall (Konvensional)"]

    hdr_row = compare_table.rows[0]
    for i, h_text in enumerate(comp_headers):
        cell = hdr_row.cells[i]
        cell.width = comp_widths[i]
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(h_text)
        r.font.bold = True
        r.font.size = Pt(9.5)
        r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
        set_cell_background(cell, "1E3A8A")
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
        set_cell_borders(cell, top="1E3A8A", bottom="1E3A8A", left="1E3A8A", right="1E3A8A")

    comp_data = [
        ("Pendekatan & Siklus", 
         "Iteratif dan Inkremental; sistem dipecah menjadi sprint mingguan menghasilkan software fungsional bertahap.", 
         "Linier dan Sekuensial; fase berikutnya hanya dapat dimulai jika fase sebelumnya telah selesai 100%."),

        ("Manajemen Perubahan (Change Flexibility)", 
         "Sangat adaptif; perubahan kebutuhan dapat diakomodasi di setiap awal sprint planning berikutnya.", 
         "Kaku; perubahan requirement di tengah jalan memerlukan proses Change Request formal yang mahal."),

        ("Delivery of Value", 
         "Cepat dan berkelanjutan; kasir dapat mencoba modul order pada Sprint 1 (minggu ke-2).", 
         "Tertunda hingga akhir siklus; software baru dapat dioperasikan setelah seluruh fase coding selesai."),

        ("Keterlibatan Pengguna (Stakeholder)", 
         "Intensif di setiap demo sprint review untuk memberikan umpan balik langsung.", 
         "Hanya intensif di awal (analisis kebutuhan) dan di akhir (User Acceptance Test)."),

        ("Integrasi Pengujian (QA & QC)", 
         "Pengujian berjalan kontinu di setiap sprint (Row 5, 9, 13 Gantt chart); cacat terdeteksi sangat dini.", 
         "Pengujian terisolasi hanya pada fase testing setelah seluruh coding selesai (risiko bottleneck tinggi)."),

        ("Fokus Dokumentasi", 
         "Secukupnya (Just enough documentation); fokus utama pada kode yang berjalan dan terdokumentasi rapi.", 
         "Sangat ketat dan formal; dokumen tebal (SRS, SDD) menjadi prasyarat sebelum baris kode pertama ditulis."),

        ("Keunggulan Utama", 
         "Sangat unggul untuk aplikasi bisnis dinamis (laundry), meminimalkan risiko salah sasaran, dan feedback cepat.", 
         "Sangat unggul untuk sistem berskala besar dengan regulasi ketat, spesifikasi tetap, dan tim lintas vendor.")
    ]

    for row_idx, data_row in enumerate(comp_data):
        row = compare_table.rows[row_idx + 1]
        bg_col = "FFFFFF" if row_idx % 2 == 0 else "F8FAFC"
        for col_idx, cell_value in enumerate(data_row):
            cell = row.cells[col_idx]
            cell.width = comp_widths[col_idx]
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(2)
            r = p.add_run(cell_value)
            r.font.size = Pt(9.0)
            if col_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(0x1E, 0x3A, 0x8A)
            set_cell_background(cell, bg_col)
            set_cell_margins(cell, top=60, bottom=60, left=100, right=100)
            set_cell_borders(cell, top="E2E8F0", bottom="E2E8F0", left="E2E8F0", right="E2E8F0")

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # =========================================================================
    # BAGIAN B: PERAN QA DAN QC (Sub-CPMK 6)
    # =========================================================================
    add_section_header("BAGIAN B: PERAN QA DAN QC (Sub-CPMK 6)")

    add_sub_header("1. Contoh Aktivitas QA dan QC pada Sistem Manajemen Laundry")
    p_b1 = doc.add_paragraph()
    p_b1.add_run(
        "Berdasarkan arsitektur codebase Laravel 12 dan lingkungan kerja Kelompok 2, "
        "berikut adalah 2 contoh konkret aktivitas Quality Assurance (QA) dan 2 contoh konkret aktivitas Quality Control (QC):"
    )

    qa_qc_boxes = [
        ("Aktivitas QA 1: Standarisasi Coding Guidelines dan Automated Linter (Laravel Pint & PSR-12)",
         "Pencegahan Cacat (Defect Prevention) pada Tingkat Proses",
         "Tim menetapkan standar penulisan kode seragam berbasis PSR-12 dan mengintegrasikan tool Laravel Pint pada seluruh environment pengembang (Adi, Alvin, Faqih, Yasir, Erna, Naila). Tool ini dikonfigurasi pada Git pre-commit hook untuk memformat kode secara otomatis sebelum di-push. Aktivitas ini mencegah inkonsistensi sintaks, typo variabel, arsitektur controller yang berantakan, serta memastikan standar kode Laravel yang seragam sehingga kode mudah dipelihara dan bebas dari defect akibat kesalahan format."),

        ("Aktivitas QA 2: Penerapan Peer Code Review dan Standar Definition of Done (DoD) pada Pull Request",
         "Penjaminan Kualitas Proses Kolaborasi Tim",
         "Kelompok 2 menerapkan aturan wajib Code Review pada repositori GitHub. Setiap cabang fitur baru (misal fitur pembayaran atau tracking) tidak boleh digabung (merge) langsung ke branch 'main' tanpa disetujui (approved) minimal oleh 1 rekan tim. Reviewer mengecek apakah query database aman dari SQL Injection (menggunakan Eloquent/Query Builder terikat), apakah validasi Form Request telah lengkap, dan apakah otentikasi role middleware kasir vs admin sudah terpasang."),

        ("Aktivitas QC 1: Eksekusi Automated Feature & Unit Testing Menggunakan PHPUnit",
         "Deteksi Cacat (Defect Detection) pada Produk Perangkat Lunak",
         "Menjalankan suite pengujian otomatis pada repository proyek, seperti 'tests/Feature/LaundryFlowTest.php' dan 'tests/Feature/AuthTest.php'. Tes ini mengeksekusi logika nyata aplikasi untuk mendeteksi apakah terjadi bug: memastikan kalkulasi tarif (berat cucian x harga layanan) menghasilkan angka yang tepat, status order berubah secara valid (Pending -> Diproses -> Selesai), dan pengguna tanpa role kasir ditolak saat mengakses endpoint transaksi."),

        ("Aktivitas QC 2: Manual User Acceptance Testing (UAT) dan Cross-Device UI Testing",
         "Validasi Cacat Produk Akhir terhadap Kebutuhan Pengguna",
         "Melakukan pengujian langsung terhadap artefak produk yang sudah jadi: menguji pencetakan nota transaksi pada printer thermal kasir (memastikan teks nota tidak terpotong, rincian biaya presisi, dan barcode terbaca scanner), serta menguji antarmuka halaman Pelacakan Cucian (Tracking) pada berbagai resolusi smartphone untuk memastikan pelanggan dapat melihat progress laundry mereka secara akurat dan responsif.")
    ]

    for title, badge, desc in qa_qc_boxes:
        p_box = doc.add_paragraph()
        p_box.paragraph_format.left_indent = Inches(0.2)
        p_box.paragraph_format.space_before = Pt(3)
        p_box.paragraph_format.space_after = Pt(3)
        r_bt = p_box.add_run(f"• {title}\n")
        r_bt.font.bold = True
        r_bt.font.size = Pt(10)
        r_bt.font.color.rgb = RGBColor(0x1E, 0x3A, 0x8A)

        r_badge = p_box.add_run(f"  [Tujuan: {badge}]\n  ")
        r_badge.font.bold = True
        r_badge.font.size = Pt(8.5)
        r_badge.font.color.rgb = RGBColor(0x05, 0x96, 0x69)

        r_desc = p_box.add_run(desc)
        r_desc.font.size = Pt(9.5)

    add_sub_header("2. Alasan QA Disebut Berorientasi Proses dan QC Berorientasi Produk")
    p_b2 = doc.add_paragraph()
    p_b2.add_run(
        "Perbedaan mendasar antara QA dan QC terletak pada objek sasaran, orientasi waktu, dan tujuan filosofisnya:\n"
    )

    orient_points = [
        ("Mengapa QA Disebut Berorientasi Proses (Process-Oriented)?",
         "QA berfokus pada METODE, ATURAN, PEDOMAN, dan ALUR KERJA yang digunakan dalam membangun perangkat lunak. "
         "QA bersifat proaktif (preventive/pencegahan), dengan asumsi bahwa jika proses produksinya dirancang, distandarisasi, "
         "dan diawasi dengan benar, maka produk yang dihasilkan secara otomatis akan memiliki kualitas tinggi dan bebas dari cacat. "
         "QA menjawab pertanyaan esensial: 'Are we building the product in the right way?' (Apakah kita membangun produk dengan cara yang benar?). "
         "Contoh QA bukan memeriksa baris kode yang error, melainkan menetapkan SOP code review, memilih standar penulisan kode, "
         "dan menyusun rancangan strategi pengujian sebelum pengembangan dimulai."),

        ("Mengapa QC Disebut Berorientasi Produk (Product-Oriented)?",
         "QC berfokus pada ARTEFAK NYATA atau HASIL AKHIR yang diproduksi (source code, executable, antarmuka, API endpoint, nota cetak, database). "
         "QC bersifat reaktif (detective/pendeteksian), dengan tujuan mengeksekusi sistem untuk menemukan cacat (bug/defect) yang masih lolos "
         "sebelum produk tersebut diserahkan ke tangan pengguna akhir. "
         "QC menjawab pertanyaan esensial: 'Did we build the right product, and does it work correctly?' (Apakah kita telah menghasilkan produk yang benar dan berfungsi sesuai spesifikasi?). "
         "Contoh QC adalah menjalankan PHPUnit test runner, melakukan penetrasi celah keamanan form login, dan memverifikasi nota pembayaran."),

        ("Analogi Komparatif pada Bisnis Laundry:",
         "• QA (Proses): Penyusunan Standar Operasional Prosedur (SOP) laundry oleh manajemen — takaran deterjen standar per 5 kg pakaian, suhu air maksimal 40°C, pemisahan pakaian putih dan pakaian mudah luntur sebelum masuk mesin, serta jadwal servis rutin mesin cuci setiap 2 minggu. Ini semua dirancang untuk mencegah pakaian rusak atau luntur sejak awal.\n"
         "• QC (Produk): Pemeriksaan fisik pakaian oleh staf kasir/checker sebelum dibungkus plastik — menginspeksi kerah baju apakah masih ada noda tertinggal, memastikan pakaian sudah disetrika rapi tanpa lipatan kusut, mencocokkan jumlah potong baju dengan nota transaksi, dan mencium apakah pakaian sudah harum. Jika ditemukan noda, baju dikembalikan ke unit cuci untuk dibersihkan ulang.")
    ]

    for o_title, o_desc in orient_points:
        p_o = doc.add_paragraph()
        p_o.paragraph_format.left_indent = Inches(0.2)
        p_o.paragraph_format.space_before = Pt(3)
        p_o.paragraph_format.space_after = Pt(3)
        r_ot = p_o.add_run(f"{o_title}\n")
        r_ot.font.bold = True
        r_ot.font.color.rgb = RGBColor(0x1E, 0x40, 0xAF)
        p_o.add_run(o_desc)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # =========================================================================
    # BAGIAN C: INTEGRASI QA/QC PADA SETIAP TAHAP (Sub-CPMK 6)
    # =========================================================================
    add_section_header("BAGIAN C: INTEGRASI QA/QC PADA SETIAP TAHAP (Sub-CPMK 6)")

    add_sub_header("1. Tabel Matriks Integrasi QA & QC pada Setiap Tahap SDLC")
    p_c1 = doc.add_paragraph()
    p_c1.add_run(
        "Tabel berikut merangkum pemetaan aktivitas penjaminan mutu (QA) dan pengendalian mutu (QC) beserta luaran/artefaknya "
        "pada setiap tahap siklus pengembangan Sistem Manajemen Laundry Kelompok 2:"
    )

    integ_table = doc.add_table(rows=6, cols=4)
    integ_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    integ_table.autofit = False

    t_widths = [Inches(1.3), Inches(2.0), Inches(2.0), Inches(1.7)]
    t_headers = ["Tahap SDLC", "Aktivitas QA\n(Pencegahan Cacat)", "Aktivitas QC\n(Pendeteksian Cacat)", "Output / Artefak"]

    hdr_row_c = integ_table.rows[0]
    for i, h_text in enumerate(t_headers):
        cell = hdr_row_c.cells[i]
        cell.width = t_widths[i]
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(h_text)
        r.font.bold = True
        r.font.size = Pt(9.0)
        r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
        set_cell_background(cell, "1E3A8A")
        set_cell_margins(cell, top=80, bottom=80, left=80, right=80)
        set_cell_borders(cell, top="1E3A8A", bottom="1E3A8A", left="1E3A8A", right="1E3A8A")

    t_data = [
        ("Requirement\nAnalysis",
         "• Menyusun template user story & Acceptance Criteria terstandar.\n"
         "• Menetapkan Definition of Ready (DoR) sebelum item masuk sprint.\n"
         "• Workshop klarifikasi spesifikasi bisnis bersama stakeholder laundry.",

         "• Requirement Walkthrough & Formal Inspection terhadap backlog.\n"
         "• Validasi kelengkapan skenario batas (edge-case) transaksi.\n"
         "• Analisis ambiguitas kebutuhan menggunakan checklist verifikasi.",

         "• Dokumen SRS / Product Backlog terverifikasi.\n"
         "• Acceptance Criteria per fitur.\n"
         "• Matriks Ketertelusuran (Requirements Traceability Matrix - RTM)."),

        ("Design",
         "• Menetapkan UI/UX Design System (warna, tipografi Tailwind, ikon).\n"
         "• Merancang arsitektur database sesuai kaidah normalisasi (3NF).\n"
         "• Pedoman arsitektur keamanan: Role-Based Access Control (RBAC) & hashing.",

         "• Design Review & Heuristic Evaluation prototipe Figma bersama tim.\n"
         "• Verifikasi integritas relasi foreign key pada ERD (orders, payments, services).\n"
         "• Validasi mockup alur transaksi kasir agar efisien dan bebas ambiguitas UI.",

         "• Prototype UI Figma yang telah disetujui (Sign-off).\n"
         "• Skema ERD & Kamus Data ternormalisasi.\n"
         "• Dokumen Arsitektur Sistem (Class & Sequence Diagram)."),

        ("Coding\n(Implementation)",
         "• Menetapkan standar coding PSR-12 & konfigurasi Laravel Pint linter.\n"
         "• Penerapan branching strategy di GitHub (Feature Branching).\n"
         "• Kebijakan wajib Peer Code Review sebelum Pull Request di-merge.",

         "• Static Code Analysis (PHPStan / SonarQube) mendeteksi syntax/dead code.\n"
         "• Pembuatan dan eksekusi Automated Unit Testing per method controller.\n"
         "• Validasi sanitasi input form (mencegah XSS dan SQL Injection).",

         "• Source code bersih, lolos linter & terstandarisasi.\n"
         "• Pull Request ter-review dengan approval log.\n"
         "• Unit Test Suite & laporan code coverage awal."),

        ("Testing",
         "• Penyusunan Test Plan & Test Strategy komprehensif.\n"
         "• Standarisasi template pembuatan Test Case (positif, negatif, batas).\n"
         "• Penyediaan environment Staging yang terisolasi dari database produksi.",

         "• Eksekusi Automated Feature Testing (PHPUnit: alur order, nota, laporan).\n"
         "• Blackbox Functional Testing pada alur kasir, bayar, & tracking.\n"
         "• User Acceptance Testing (UAT) bersama calon pengguna laundry.",

         "• Test Execution Report & Status Pengujian.\n"
         "• Bug / Defect Log pada Issue Tracker GitHub.\n"
         "• Berita Acara UAT & User Acceptance Sign-off."),

        ("Deployment &\nMaintenance",
         "• Otomasi deployment pipeline (CI/CD) ke server staging/production.\n"
         "• Penyusunan Release Checklist & SOP migrasi database.\n"
         "• Prosedur automated backup basis data harian dan Disaster Recovery Plan.",

         "• Smoke Testing & Sanity Testing langsung pasca-deployment rilis.\n"
         "• Verifikasi konfigurasi environment (.env) dan koneksi database produksi.\n"
         "• Monitoring log error berkala (Laravel Log Viewer) dan pelaporan bug.",

         "• Live Production Application yang stabil.\n"
         "• Deployment Release Notes & Changelog.\n"
         "• Dashboard Monitoring & Laporan Maintenance Berkala.")
    ]

    for row_idx, row_values in enumerate(t_data):
        row = integ_table.rows[row_idx + 1]
        bg_col = "FFFFFF" if row_idx % 2 == 0 else "F8FAFC"
        for col_idx, cell_text in enumerate(row_values):
            cell = row.cells[col_idx]
            cell.width = t_widths[col_idx]
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(2)
            r = p.add_run(cell_text)
            r.font.size = Pt(8.5)
            if col_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(0x1E, 0x3A, 0x8A)
            set_cell_background(cell, bg_col)
            set_cell_margins(cell, top=50, bottom=50, left=70, right=70)
            set_cell_borders(cell, top="CBD5E1", bottom="CBD5E1", left="CBD5E1", right="CBD5E1")

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # Soal 2
    add_sub_header("2. Kesimpulan: Risiko Jika QA/QC Hanya Dilakukan di Akhir Fase Pengkodean")
    p_c2 = doc.add_paragraph()
    p_c2.add_run(
        "Melakukan aktivitas QA dan QC hanya pada akhir fase pengkodean (fase testing konvensional / Waterfall trap) "
        "merupakan anti-pattern kritis dalam rekayasa perangkat lunak modern. "
        "Berdasarkan analisis rekayasa mutu, terdapat lima dampak risiko fatal yang dapat terjadi:"
    )

    risks = [
        ("Lonjakan Biaya Perbaikan Cacat Eksponensial (Cost of Defect Multiplier)",
         "Berdasarkan kurva Barry Boehm, biaya memperbaiki bug yang terdeteksi di fase akhir atau produksi "
         "bisa membengkak 30 hingga 100 kali lebih mahal dibandingkan jika dicegah pada tahap requirement atau design. "
         "Misalnya, jika kesalahan desain relasi database transaksi laundry baru disadari saat sistem sudah jadi, "
         "tim harus merombak skema basis data, mengubah puluhan migration file, merefaktor seluruh model Eloquent, "
         "dan menulis ulang tampilan blade terkait."),

        ("Cacat Struktural dan Arsitektural yang Sangat Sulit Diperbaiki",
         "Cacat yang berakar dari analisis kebutuhan atau desain arsitektur (seperti celah keamanan hak akses role atau ketidakmampuan menangani konkurensi nomor nota) "
         "bersifat sistemik. Memperbaikinya secara terburu-buru di akhir pengkodean seringkali hanya menghasilkan solusi tambal sulam "
         "(quick-and-dirty monkey patch) yang rapuh dan merusak integritas arsitektur sistem."),

        ("Terjadinya Testing Bottleneck dan Keterlambatan Rilis (Schedule Slipping)",
         "Ketika ribuan baris kode dilemparkan ke tim pengujian sekaligus tanpa melalui continuous testing di tiap sprint, "
         "fase pengujian akan mengalami bottleneck yang parah. QA/QC akan kebanjiran ratusan temuan bug dalam waktu bersamaan, "
         "yang berujung pada penundaan jadwal rilis produk (schedule slipping) atau terpaksa merilis aplikasi dalam kondisi tidak stabil."),

        ("Penurunan Moral Tim Pengembang dan Technical Debt Masif",
         "Developer mengalami stres tinggi (burnout) karena harus melakukan bug fixing di bawah tekanan tenggat waktu yang mencekik. "
         "Perbaikan darurat di bawah tekanan tinggi hampir selalu memicu efek samping (regression bugs), "
         "di mana perbaikan satu modul justru merusak dua modul lain yang sebelumnya berfungsi normal."),

        ("Risiko Kegagalan Bisnis Fatal dan Kehilangan Reputasi Pengguna",
         "Bagi bisnis laundry, jika bug kritis lolos ke tahap operasional nyata — seperti salah kalkulasi total tagihan pelanggan, "
         "nota tidak dapat dicetak saat antrean panjang pelanggan kasir, atau hilangnya riwayat orderan cucian — "
         "dampaknya bukan sekadar masalah teknis, melainkan kerugian finansial langsung bagi pemilik usaha dan hilangnya kepercayaan pelanggan laundry secara permanen.")
    ]

    for r_title, r_desc in risks:
        p_risk = doc.add_paragraph()
        p_risk.paragraph_format.left_indent = Inches(0.2)
        p_risk.paragraph_format.space_before = Pt(3)
        p_risk.paragraph_format.space_after = Pt(2)
        r_rt = p_risk.add_run(f"• {r_title}:\n  ")
        r_rt.font.bold = True
        r_rt.font.color.rgb = RGBColor(0x99, 0x1B, 0x1B) # Crimson Red
        p_risk.add_run(r_desc)

    p_sol = doc.add_paragraph()
    p_sol.paragraph_format.space_before = Pt(6)
    p_sol.paragraph_format.space_after = Pt(4)
    r_sol_title = p_sol.add_run("Rekomendasi Solusi Mutu: Penerapan Paradigma 'Shift-Left Testing'\n")
    r_sol_title.font.bold = True
    r_sol_title.font.color.rgb = RGBColor(0x06, 0x5F, 0x46) # Emerald Green
    p_sol.add_run(
        "Untuk memitigasi seluruh risiko di atas, Kelompok 2 secara konsisten menerapkan paradigma 'Shift-Left Testing', "
        "yaitu menggeser seluruh aktivitas penjaminan kualitas (QA) dan pengendalian kualitas (QC) ke tahap seawal mungkin dalam SDLC. "
        "Kualitas dibangun sejak perumusan User Stories dan perancangan skema sistem (QA proaktif), "
        "serta dikontrol secara kontinu melalui milestone pengujian sistem di akhir setiap sprint (Row 5, 9, dan 13 Gantt Chart). "
        "Dengan pendekatan ini, cacat dicegah sebelum muncul dalam kode, bug dideteksi dalam siklus hitungan hari bukan bulan, "
        "dan Sistem Manajemen Laundry dapat dirilis dengan jaminan keandalan, stabilitas, dan performa yang optimal."
    )

    # Save documents
    target_path = "Tugas/6. Laporan_Integrasi QA QC pada Model SDLC - Kelompok 2.docx"
    target_path_original_name = "Tugas/6. Tugas_Integrasi QA  QC pada Model SDLC_Selesai.docx"
    doc.save(target_path)
    doc.save(target_path_original_name)
    print(f"Laporan berhasil dibuat dan disimpan di:\n1. {target_path}\n2. {target_path_original_name}")

if __name__ == "__main__":
    create_document()
