<div class="row justify-content-center">
  <div class="col-xl-9 col-xxl-10">
    <div class="card consent-card">
        <div class="consent-document-header">
          <div>
            <span class="consent-eyebrow">Nurture for Growth</span>
            <h4 class="consent-title">INFORMED CONSENT<br><span>Layanan Psikologi</span></h4>
          </div>
          <div class="consent-header-mark"><i class="fas fa-file-signature"></i></div>
        </div>
        <?php if ($informedConsent): ?>
          <div class="consent-already-complete">
            <div class="consent-already-icon"><i class="fas fa-circle-check"></i></div>
            <div>
              <h5>Anda sudah mengisi Informed Consent Layanan Psikologi</h5>
              <p>Form ini hanya perlu diisi satu kali. Data persetujuan Anda sudah tersimpan dan dapat digunakan untuk proses booking konseling berikutnya.</p>
              <div class="consent-already-meta" style="font-size: 1.10rem;">
                <span><strong>Disetujui:</strong> <?= format_date($informedConsent['signed_at'], 'd M Y H:i') ?></span>
                <span><strong>Nama:</strong> <?= e($informedConsent['signature_name']) ?></span>
              </div>
              <a href="<?= base_url('/consultation/create?' . http_build_query(array_filter(['provider_id' => (int) $providerId, 'matched' => $isMatched ? '1' : null, 'consent_checked' => '1']))) ?>" class="btn btn-primary btn-sm mt-3"><i class="fas fa-calendar-check me-1"></i>Lanjut ke Booking Konseling</a>
            </div>
          </div>
        <?php else: ?>
        <form method="POST" action="<?= base_url('/consultation/consent') ?>">
          <?= \Core\Csrf::field() ?>
          <input type="hidden" name="provider_id" value="<?= (int) $providerId ?>">
          <input type="hidden" name="matched" value="<?= $isMatched ? '1' : '0' ?>">

          <section class="consent-section consent-client-data">
            <div class="consent-section-heading"><span class="consent-section-kicker">01</span><div><h6>Data Klien</h6><p>Pastikan informasi berikut sudah sesuai.</p></div></div>
            <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small" for="consentFullName">Nama Klien</label>
              <input id="consentFullName" name="full_name" class="form-control" value="<?= e($patient['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small" for="consentBirthDate">Tanggal Lahir</label>
              <input id="consentBirthDate" type="date" name="birth_date" class="form-control" value="<?= e($patient['birth_date'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small" for="guardianName">Nama Orang Tua/Wali (jika diperlukan)</label>
              <input id="guardianName" name="guardian_name" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label small" for="consentDate">Tanggal</label>
              <input id="consentDate" type="date" class="form-control" value="<?= date('Y-m-d') ?>" disabled>
            </div>
            </div>
          </section>

          <section class="consent-section consent-reading-section">
            <p class="consent-lead">Saya menyatakan telah memperoleh informasi yang memadai mengenai layanan psikologi yang akan saya/anak saya terima di <strong>Nurture for Growth (NFG)</strong> dan memberikan persetujuan secara sadar untuk mengikuti layanan tersebut.</p>
            <div class="consent-section-heading"><span class="consent-section-kicker">02</span><div><h6>Pernyataan Persetujuan</h6><p>Baca setiap bagian, lalu centang sebagai tanda bahwa Anda memahami dan menyetujuinya.</p></div></div>

          <div class="consent-points">
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_process" value="1" required>
              <span class="form-check-label"><strong>1. Proses Layanan</strong><br>Layanan psikologi dapat berupa konseling, psikoterapi, konsultasi, asesmen/pemeriksaan psikologis, wawancara, observasi, penggunaan alat ukur psikologis, maupun metode profesional lain sesuai kebutuhan dan pertimbangan Psikolog.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_benefits" value="1" required>
              <span class="form-check-label"><strong>2. Manfaat dan Keterbatasan</strong><br>Saya telah memperoleh penjelasan mengenai tujuan, proses, manfaat, serta kemungkinan ketidaknyamanan emosional yang dapat muncul selama layanan. Saya memahami bahwa layanan psikologi merupakan proses kolaboratif dan <strong>tidak menjamin hasil, perubahan, diagnosis, rekomendasi, maupun penyelesaian masalah tertentu dalam jumlah sesi atau waktu tertentu</strong>.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_confidentiality" value="1" required>
              <span class="form-check-label"><strong>3. Kerahasiaan</strong><br>Informasi yang diperoleh selama layanan akan dijaga kerahasiaannya sesuai standar profesi dan ketentuan hukum yang berlaku. Informasi hanya dapat dibagikan apabila diperlukan untuk kepentingan profesional, keselamatan, kewajiban hukum, atau kepada pihak lain atas persetujuan saya sesuai ketentuan yang berlaku.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_data" value="1" required>
              <span class="form-check-label"><strong>4. Data dan Dokumentasi</strong><br>Saya memberikan persetujuan kepada NFG untuk mengumpulkan, menyimpan, dan memproses data pribadi, informasi psikologis, catatan layanan, serta hasil asesmen yang diperlukan untuk penyelenggaraan layanan, administrasi, dokumentasi profesional, dan pemenuhan kewajiban hukum. Untuk klien anak, persetujuan diberikan oleh orang tua/wali yang berwenang.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_assessment" value="1" required>
              <span class="form-check-label"><strong>5. Asesmen Psikologis</strong><br>Apabila menjalani asesmen, saya memahami bahwa hasil pemeriksaan didasarkan pada kondisi dan informasi yang tersedia pada saat pemeriksaan. Hasil asesmen digunakan sesuai tujuan pemeriksaan yang telah disepakati dan tidak selalu dapat digunakan untuk tujuan lain.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_emergency" value="1" required>
              <span class="form-check-label"><strong>6. Rujukan dan Kondisi Darurat</strong><br>Psikolog dapat merekomendasikan rujukan kepada profesional atau fasilitas lain apabila kebutuhan saya berada di luar cakupan atau kompetensi layanan NFG. Saya memahami bahwa layanan terjadwal NFG <strong>bukan layanan kegawatdaruratan 24 jam</strong>.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_rights" value="1" required>
              <span class="form-check-label"><strong>7. Hak Klien</strong><br>Saya berhak memperoleh penjelasan mengenai layanan, mengajukan pertanyaan, menjaga kerahasiaan informasi saya, menyampaikan keluhan, serta mendiskusikan perubahan atau penghentian layanan dengan Psikolog.</span>
            </label>
            <label class="form-check consent-point">
              <input class="form-check-input" type="checkbox" name="understands_administration" value="1" required>
              <span class="form-check-label"><strong>8. Ketentuan Administratif</strong><br>Saya memahami bahwa booking, pembayaran, perubahan jadwal, pembatalan, keterlambatan, dan ketidakhadiran mengikuti <strong>Terms &amp; Conditions NFG</strong> yang telah disampaikan kepada saya.</span>
            </label>
          </div>
          </section>

          <section class="consent-approval-section">
            <div class="consent-section-heading"><span class="consent-section-kicker">03</span><div><h6>Pernyataan Persetujuan Akhir</h6><p>Lengkapi nama sebagai tanda tangan elektronik.</p></div></div>
            <p>Dengan menandatangani/memberikan persetujuan pada dokumen ini, saya menyatakan bahwa saya telah membaca atau memperoleh penjelasan mengenai isi Informed Consent ini, memiliki kesempatan untuk bertanya, memahami informasi yang diberikan, dan <strong>secara sukarela menyetujui pelaksanaan layanan psikologi di NFG</strong>.</p>
            <p class="fw-bold mb-2">Klien / Orang Tua / Wali</p>
            <label class="form-label small" for="consentSignature">Tanda tangan/Persetujuan elektronik</label>
            <input id="consentSignature" name="signature_name" class="form-control consent-signature-input" placeholder="Ketik nama lengkap" required>
            <p class="text-muted small mt-2 mb-0"><i class="fas fa-lock me-1"></i>Persetujuan ini akan tersimpan di data client dan dapat dilihat oleh psikolog yang menangani.</p>
          </section>

          <div class="consent-actions d-flex flex-column flex-sm-row justify-content-between gap-2">
            <a class="btn btn-outline-secondary" href="<?= base_url('/consultation/create') ?>">Kembali</a>
            <button class="btn btn-primary" type="submit"><i class="fas fa-file-signature me-1"></i> Simpan Persetujuan &amp; Lanjutkan</button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
