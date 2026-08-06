# from docx import Document
# from docx.shared import Pt, Cm, RGBColor
# from docx.enum.text import WD_ALIGN_PARAGRAPH
# from docx.enum.table import WD_TABLE_ALIGNMENT
# from docx.oxml.ns import qn

# doc = Document()

# # Margin standar surat resmi
# section = doc.sections[0]
# section.top_margin = Cm(2.5)
# section.bottom_margin = Cm(2.5)
# section.left_margin = Cm(3)
# section.right_margin = Cm(2.5)

# style = doc.styles['Normal']
# style.font.name = 'Calibri'
# style.font.size = Pt(11)


# def add_para(text='', bold=False, size=11, align=None, space_after=6, italic=False, color=None):
#     p = doc.add_paragraph()
#     p.paragraph_format.space_after = Pt(space_after)
#     if align:
#         p.alignment = align
#     run = p.add_run(text)
#     run.bold = bold
#     run.italic = italic
#     run.font.size = Pt(size)
#     if color:
#         run.font.color.rgb = color
#     return p


# def add_placeholder_line(label, placeholder, colon=True):
#     p = doc.add_paragraph()
#     p.paragraph_format.space_after = Pt(2)
#     tab_stops = p.paragraph_format.tab_stops
#     tab_stops.add_tab_stop(Cm(5))
#     r1 = p.add_run(label)
#     r1.font.size = Pt(11)
#     p.add_run('\t')
#     r2 = p.add_run((': ' if colon else '') + '{{' + placeholder + '}}')
#     r2.font.size = Pt(11)
#     return p


# # ===== KOP SURAT =====
# kop = doc.add_paragraph()
# kop.alignment = WD_ALIGN_PARAGRAPH.CENTER
# r = kop.add_run('PT. SGN')
# r.bold = True
# r.font.size = Pt(16)

# sub = doc.add_paragraph()
# sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
# r = sub.add_run('Jl. Contoh Alamat Perusahaan No. 1, Kota, Indonesia\nTelp: (021) 000-0000  |  Email: hr@sgn.co.id')
# r.font.size = Pt(9)

# # garis pemisah kop
# p = doc.add_paragraph()
# pPr = p._p.get_or_add_pPr()
# pBdr = pPr.makeelement(qn('w:pBdr'), {})
# bottom = pPr.makeelement(qn('w:bottom'), {qn('w:val'): 'single', qn('w:sz'): '12', qn('w:space'): '1', qn('w:color'): '000000'})
# pBdr.append(bottom)
# pPr.append(pBdr)

# doc.add_paragraph()

# # ===== JUDUL =====
# title = doc.add_paragraph()
# title.alignment = WD_ALIGN_PARAGRAPH.CENTER
# r = title.add_run('PERJANJIAN KERJA {{JENIS_KONTRAK}}')
# r.bold = True
# r.underline = True
# r.font.size = Pt(13)

# nomor = doc.add_paragraph()
# nomor.alignment = WD_ALIGN_PARAGRAPH.CENTER
# r = nomor.add_run('Nomor: {{NOMOR_KONTRAK}}')
# r.font.size = Pt(11)

# doc.add_paragraph()

# # ===== PEMBUKA =====
# add_para(
#     'Pada hari ini, {{TANGGAL_KONTRAK}}, bertempat di kantor PT. SGN, kedua belah pihak yang '
#     'bertanda tangan di bawah ini telah sepakat untuk mengadakan Perjanjian Kerja {{JENIS_KONTRAK}} '
#     'dengan ketentuan dan syarat-syarat sebagai berikut:'
# )

# doc.add_paragraph()

# # ===== PIHAK PERTAMA =====
# add_para('PIHAK PERTAMA', bold=True, space_after=2)
# add_placeholder_line('Nama', 'NAMA_PENANDATANGAN')
# add_placeholder_line('Jabatan', 'JABATAN_PENANDATANGAN')
# add_para('Dalam hal ini bertindak untuk dan atas nama PT. SGN, selanjutnya disebut sebagai PIHAK PERTAMA.', space_after=10)

# # ===== PIHAK KEDUA =====
# add_para('PIHAK KEDUA', bold=True, space_after=2)
# add_placeholder_line('Nama', 'NAMA_KARYAWAN')
# add_placeholder_line('NIK', 'NIK_KARYAWAN')
# add_placeholder_line('Tempat, Tanggal Lahir', 'TEMPAT_TANGGAL_LAHIR')
# add_placeholder_line('Jenis Kelamin', 'JENIS_KELAMIN')
# add_placeholder_line('Alamat', 'ALAMAT_KARYAWAN')
# add_para('Selanjutnya disebut sebagai PIHAK KEDUA.', space_after=10)

# add_para(
#     'PIHAK PERTAMA dan PIHAK KEDUA selanjutnya secara bersama-sama disebut PARA PIHAK, dan '
#     'secara sendiri-sendiri disebut PIHAK, dengan ini sepakat untuk mengikatkan diri dalam '
#     'Perjanjian Kerja dengan ketentuan sebagai berikut:'
# )

# doc.add_paragraph()

# # ===== PASAL-PASAL =====
# def add_pasal(nomor_pasal, judul):
#     p = doc.add_paragraph()
#     p.alignment = WD_ALIGN_PARAGRAPH.CENTER
#     r = p.add_run(f'PASAL {nomor_pasal}')
#     r.bold = True
#     r.font.size = Pt(11)
#     p2 = doc.add_paragraph()
#     p2.alignment = WD_ALIGN_PARAGRAPH.CENTER
#     r2 = p2.add_run(judul)
#     r2.bold = True
#     r2.font.size = Pt(11)


# add_pasal(1, 'JABATAN DAN PENEMPATAN')
# add_para(
#     'PIHAK KEDUA diangkat dan ditempatkan untuk bekerja pada PIHAK PERTAMA dengan jabatan '
#     'sebagai {{JABATAN_KARYAWAN}} di bagian/departemen {{DEPARTEMEN}}.'
# )

# add_pasal(2, 'JANGKA WAKTU PERJANJIAN')
# add_para(
#     'Perjanjian Kerja ini berlaku terhitung mulai tanggal {{TANGGAL_MULAI}} sampai dengan '
#     'tanggal {{TANGGAL_SELESAI}}, dan dapat diperpanjang atau diperbaharui sesuai kesepakatan '
#     'PARA PIHAK serta ketentuan peraturan perundang-undangan yang berlaku.'
# )

# add_pasal(3, 'GAJI DAN TUNJANGAN')
# add_para(
#     'PIHAK PERTAMA akan membayar gaji pokok kepada PIHAK KEDUA sebesar Rp {{GAJI_POKOK}} '
#     '(terbilang) per bulan, dibayarkan selambat-lambatnya pada tanggal 5 (lima) setiap bulannya, '
#     'dipotong pajak dan iuran sesuai ketentuan yang berlaku.'
# )

# add_pasal(4, 'HAK DAN KEWAJIBAN')
# add_para(
#     'PIHAK KEDUA wajib melaksanakan pekerjaan dengan sebaik-baiknya, mematuhi peraturan '
#     'perusahaan, serta menjaga kerahasiaan informasi perusahaan. PIHAK PERTAMA wajib '
#     'memberikan hak-hak PIHAK KEDUA sesuai ketentuan ketenagakerjaan yang berlaku.'
# )

# add_pasal(5, 'BERAKHIRNYA PERJANJIAN')
# add_para(
#     'Perjanjian Kerja ini berakhir apabila jangka waktu sebagaimana dimaksud pada Pasal 2 telah '
#     'berakhir, atau karena sebab lain sesuai peraturan perundang-undangan yang berlaku.'
# )

# add_pasal(6, 'PENUTUP')
# add_para(
#     'Demikian Perjanjian Kerja ini dibuat dengan sebenarnya dan ditandatangani oleh PARA PIHAK '
#     'dalam keadaan sadar tanpa paksaan dari pihak manapun, untuk dipergunakan sebagaimana mestinya.'
# )

# if False:
#     pass

# # Catatan tambahan (opsional, hanya tampil jika diisi controller)
# add_para('Catatan tambahan: {{CATATAN}}', italic=True, size=10)

# doc.add_paragraph()
# doc.add_paragraph()

# # ===== TANDA TANGAN =====
# table = doc.add_table(rows=5, cols=2)
# table.alignment = WD_TABLE_ALIGNMENT.CENTER

# cells_text = {
#     (0, 0): 'PIHAK KEDUA,',
#     (0, 1): 'PIHAK PERTAMA,',
#     (4, 0): '{{NAMA_KARYAWAN}}',
#     (4, 1): '{{NAMA_PENANDATANGAN}}',
# }

# for (row, col), text in cells_text.items():
#     cell = table.rows[row].cells[col]
#     p = cell.paragraphs[0]
#     p.alignment = WD_ALIGN_PARAGRAPH.CENTER
#     r = p.add_run(text)
#     if row == 4:
#         r.bold = True
#         r.underline = True

# # baris kosong untuk ruang tanda tangan (row 1-3)
# for row in range(1, 4):
#     for col in range(2):
#         cell = table.rows[row].cells[col]
#         cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER

# # hilangkan border tabel tanda tangan
# tbl = table._tbl
# tblPr = tbl.tblPr
# borders = tblPr.makeelement(qn('w:tblBorders'), {})
# for edge in ('top', 'left', 'bottom', 'right', 'insideH', 'insideV'):
#     el = tblPr.makeelement(qn(f'w:{edge}'), {qn('w:val'): 'none', qn('w:sz'): '0', qn('w:space'): '0', qn('w:color'): 'auto'})
#     borders.append(el)
# tblPr.append(borders)

# out_path = '/home/claude/template_kontrak_kerja.docx'
# doc.save(out_path)
# print('saved:', out_path)
