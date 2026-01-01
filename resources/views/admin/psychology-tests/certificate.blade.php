<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Tes Psikologi SIM</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            width: 210mm;
            height: 297mm;
            position: relative;
            background: white;
        }

        .header-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 180px;
            background: linear-gradient(135deg, #c41e3a 0%, #8b1428 50%, #4a0a14 100%);
            clip-path: polygon(0 0, 100% 0, 100% 85%, 50% 100%, 0 85%);
        }

        .header-logos {
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            display: table;
            width: 100%;
            padding: 0 40px;
            z-index: 10;
        }

        .logo-left {
            display: table-cell;
            text-align: left;
        }

        .logo-right {
            display: table-cell;
            text-align: right;
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.3);
            border: 2px solid white;
            display: inline-block;
            margin: 0 5px;
        }

        .content {
            position: relative;
            padding: 200px 60px 40px 60px;
            z-index: 5;
        }

        .title {
            text-align: center;
            margin-bottom: 10px;
        }

        .title h1 {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }

        .title h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .cert-number {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .intro-text {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .profile-section {
            margin-bottom: 30px;
        }

        .profile-wrapper {
            display: table;
            width: 100%;
        }

        .profile-photo {
            display: table-cell;
            width: 150px;
            vertical-align: top;
            padding-right: 30px;
        }

        .photo-box {
            width: 120px;
            height: 150px;
            border: 2px solid #333;
            background: #f0f0f0;
        }

        .profile-data {
            display: table-cell;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            font-size: 13px;
            line-height: 1.8;
        }

        .data-table tr {
            height: 28px;
        }

        .data-table td:first-child {
            width: 180px;
            font-weight: 500;
        }

        .data-table td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        .data-table td:last-child {
            font-weight: bold;
        }

        .requirements {
            text-align: center;
            font-size: 13px;
            margin: 25px 0;
        }

        .requirements-bold {
            font-weight: bold;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border: 2px solid #333;
        }

        .signature-section {
            text-align: right;
            margin-top: 30px;
            padding-right: 60px;
        }

        .signature-date {
            font-size: 13px;
            margin-bottom: 60px;
        }

        .signature-name {
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-title {
            font-size: 12px;
            margin-top: 5px;
        }

        .footer-gradient {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(135deg, #4a0a14 0%, #8b1428 50%, #c41e3a 100%);
            clip-path: polygon(0 15%, 50% 0, 100% 15%, 100% 100%, 0 100%);
        }

        .footer-content {
            position: absolute;
            bottom: 30px;
            right: 40px;
            text-align: right;
            z-index: 10;
            color: white;
        }

        .footer-text {
            font-size: 10px;
            color: white;
        }

        .eppsi-footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            z-index: 10;
        }

        .eppsi-text {
            font-size: 9px;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="header-gradient"></div>

    <div class="header-logos">
        <div class="logo-left">
            @if(file_exists(public_path('assets/logo/logo-polisi.png')))
                <div class="logo-placeholder"></div>
            @else
                <div class="logo-placeholder"></div>
            @endif
            @if(file_exists(public_path('assets/logo/caina-logo.png')))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/logo/caina-logo.png'))) }}" style="width: 80px; height: auto;" alt="Logo">
            @else
                <div class="logo-placeholder"></div>
            @endif
        </div>
        <div class="logo-right">
            <div class="logo-placeholder"></div>
        </div>
    </div>

    <div class="content">
        <div class="title">
            <h1>SERTIFIKAT</h1>
            <h2>HASIL TES PSIKOLOGI SIM</h2>
        </div>

        <div class="cert-number">
            No Sertifikat : {{ $certificateNumber }}
        </div>

        <div class="intro-text">
            Sertifikat ini diberikan kepada:
        </div>

        <div class="profile-section">
            <div class="profile-wrapper">
                <div class="profile-photo">
                    <div class="photo-box"></div>
                </div>
                <div class="profile-data">
                    <table class="data-table">
                        <tr>
                            <td>Nama Lengkap</td>
                            <td>:</td>
                            <td>{{ strtoupper($data->name) }}</td>
                        </tr>
                        <tr>
                            <td>NIK</td>
                            <td>:</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Jenis kelamin</td>
                            <td>:</td>
                            <td>{{ $data->gender === 'male' ? 'LAKI-LAKI' : 'PEREMPUAN' }}</td>
                        </tr>
                        <tr>
                            <td>Tempat, Tanggal Lahir</td>
                            <td>:</td>
                            <td>{{ strtoupper($birthPlace) }}, {{ $birthDate }}</td>
                        </tr>
                        <tr>
                            <td>Usia</td>
                            <td>:</td>
                            <td>{{ $data->age }} TAHUN</td>
                        </tr>
                        <tr>
                            <td>Jenis SIM</td>
                            <td>:</td>
                            <td>{{ strtoupper($data->sim ? $data->sim->name : '-') }}</td>
                        </tr>
                        <tr>
                            <td>Golongan SIM</td>
                            <td>:</td>
                            <td>{{ $data->groupSim ? $data->groupSim->name : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Domisili</td>
                            <td>:</td>
                            <td>{{ strtoupper($data->domicile ?: 'KABUPATEN TANGERANG') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="requirements">
            <span class="requirements-bold">MEMENUHI SYARAT</span> dalam mengajukan permohonan SIM.<br>
            <span class="requirements-bold">Sertifikat ini berlaku sampai dengan 1 Maret 2026</span>
        </div>

        <div class="qr-section">
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrCodeUrl) }}"
                     style="width: 100%; height: 100%;" alt="QR Code">
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-date">{{ $printDate }}</div>
            <div style="margin-bottom: 80px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode('Verified by ' . $certificateNumber) }}"
                     style="width: 80px; height: 80px;" alt="Signature QR">
            </div>
            <div class="signature-name">( Pamila Maysari M.Psi, Psikolog )</div>
            <div class="signature-title">Psikolog</div>
        </div>
    </div>

    <div class="eppsi-footer">
        <div class="eppsi-text">
            <strong>EPPSI</strong><br>
            Platform Tes Psikologi SIM<br>
            dibuat oleh PT. Central Asesmen Indonesia
        </div>
    </div>

    <div class="footer-gradient"></div>

    <div class="footer-content">
        <div class="footer-text">
            <strong>Sertifikat Digital Tes Psikologi SIM</strong><br>
            powered by<br>
            <strong>CENTRAL ASESMEN INDONESIA</strong>
        </div>
    </div>
</body>
</html>