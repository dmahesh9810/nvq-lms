<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificate of Competency — {{ $certificate->certificate_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        background: #fff;
        color: #1a1a2e;
        width: 297mm;
        height: 210mm;
        overflow: hidden;
    }

    .page {
        width: 297mm;
        height: 210mm;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    /* Outer decorative border */
    .outer-border {
        position: absolute;
        top: 8mm;
        left: 8mm;
        right: 8mm;
        bottom: 8mm;
        border: 3px solid #b8975a;
    }

    /* Inner border */
    .inner-border {
        position: absolute;
        top: 12mm;
        left: 12mm;
        right: 12mm;
        bottom: 12mm;
        border: 1px solid #b8975a;
    }

    /* Gold corner ornaments (CSS only, dompdf-safe) */
    .corner {
        position: absolute;
        width: 12mm;
        height: 12mm;
        border-color: #b8975a;
        border-style: solid;
    }
    .corner-tl { top: 5mm; left: 5mm; border-width: 3px 0 0 3px; }
    .corner-tr { top: 5mm; right: 5mm; border-width: 3px 3px 0 0; }
    .corner-bl { bottom: 5mm; left: 5mm; border-width: 0 0 3px 3px; }
    .corner-br { bottom: 5mm; right: 5mm; border-width: 0 3px 3px 0; }

    .content {
        position: relative;
        z-index: 10;
        text-align: center;
        padding: 0 20mm;
        width: 100%;
    }

    .org-name {
        font-size: 13pt;
        font-weight: bold;
        letter-spacing: 4px;
        color: #b8975a;
        text-transform: uppercase;
        margin-bottom: 3mm;
    }

    .org-sub {
        font-size: 9pt;
        color: #666;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 6mm;
    }

    .divider {
        border: none;
        border-top: 1px solid #b8975a;
        width: 60%;
        margin: 0 auto 6mm auto;
    }

    .cert-title {
        font-size: 28pt;
        font-weight: bold;
        color: #1a1a2e;
        letter-spacing: 2px;
        margin-bottom: 2mm;
    }

    .cert-subtitle {
        font-size: 12pt;
        color: #555;
        font-style: italic;
        margin-bottom: 6mm;
    }

    .presented-to {
        font-size: 10pt;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 2mm;
    }

    .student-name {
        font-size: 26pt;
        font-weight: bold;
        color: #1a3a6b;
        border-bottom: 2px solid #b8975a;
        display: inline-block;
        padding-bottom: 1mm;
        margin-bottom: 5mm;
    }

    .course-label {
        font-size: 10pt;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 1mm;
    }

    .course-name {
        font-size: 16pt;
        font-weight: bold;
        color: #1a1a2e;
        margin-bottom: 2mm;
    }

    .nvq-level {
        font-size: 10pt;
        color: #b8975a;
        margin-bottom: 6mm;
    }

    .nvq-complete-text {
        font-size: 10pt;
        color: #444;
        margin-bottom: 6mm;
        font-style: italic;
    }

    .footer-grid {
        display: table;
        width: 80%;
        margin: 0 auto;
        border-top: 1px solid #ddd;
        padding-top: 4mm;
    }

    .footer-cell {
        display: table-cell;
        width: 33.33%;
        text-align: center;
        vertical-align: top;
    }

    .footer-label {
        font-size: 7pt;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1mm;
    }

    .footer-value {
        font-size: 9pt;
        font-weight: bold;
        color: #333;
    }

    .cert-number {
        font-size: 8pt;
        color: #b8975a;
        font-family: 'DejaVu Sans Mono', monospace;
    }

    .nvq-badge {
        display: inline-block;
        background: #1a3a6b;
        color: #fff;
        font-size: 9pt;
        font-weight: bold;
        padding: 2mm 6mm;
        letter-spacing: 2px;
        margin-bottom: 4mm;
    }
</style>
</head>
<body>
<div class="page">
    <!-- Decorative borders -->
    <div class="outer-border"></div>
    <div class="inner-border"></div>

    <!-- Corner ornaments -->
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <!-- Certificate content -->
    <div class="content">

        <div class="org-name">IQBrave Training Centre</div>
        <div class="org-sub">National Vocational Qualification Programme</div>

        <hr class="divider">

        <div class="cert-title">Certificate of Competency</div>
        <div class="cert-subtitle">This is to proudly certify that</div>

        <div class="presented-to">the following student has demonstrated full competency</div>

        <div class="student-name">{{ $certificate->user->name }}</div>

        <div class="course-label">has successfully completed the NVQ course</div>
        <div class="course-name">{{ $certificate->course->title }}</div>

        @if(!empty($certificate->course->level))
        <div class="nvq-level">{{ $certificate->course->level }}</div>
        @endif

        <div class="nvq-complete-text">
            having demonstrated competency in all required units in accordance with NVQ standards.
        </div>

        <div class="nvq-badge">&#10003; COMPETENT</div>

        <!-- Footer row: date | cert number | signature -->
        <div class="footer-grid">
            <div class="footer-cell">
                <div class="footer-label">Date of Issue</div>
                <div class="footer-value">{{ $certificate->issued_at->format('d F Y') }}</div>
            </div>
            <div class="footer-cell">
                <div class="footer-label">Certificate Number</div>
                <div class="cert-number">{{ $certificate->certificate_number }}</div>
            </div>
            <div class="footer-cell">
                <div class="footer-label">Authorised By</div>
                <div class="footer-value">IQBrave Training Centre</div>
            </div>
        </div>

    </div>
</div>
</body>
</html>
