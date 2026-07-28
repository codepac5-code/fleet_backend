<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- <link href="https://fonts.googleapis.com/css2?family=Archivo+Narrow:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet"> --}}

    <title>IKA Invoice 2000536318</title>
    <style>


        @page {
          size: A4;
          margin: 20mm 22mm 25mm 22mm;
        }

        body {
          margin: 0;
          padding: 0;
          font-family: 'Roboto', sans-serif;
          color: #333;
          font-size: 11px;
          line-height: 1.35;
          background-color: #fff;
        }

        /* ===== HEADER ===== */
        .header {
          position: relative;
          width: 100%;
          height: 90px;
          margin-top: 15px;
          border-bottom: 1px solid #e0e0e0;
          padding-bottom: 10px;
        }

        .header-left {
          position: absolute;
          left: 60px;
          top: 8px;
          line-height: 1.25;
        }

        .header-left strong {
          font-weight: 700;
          font-family: 'Archivo Narrow', sans-serif;
        }

        .header-right {
          position: absolute;
          right: 60px;
          top: -5px;
        }

        .header-right img {
          width: 105px;
          height: auto;
        }

        /* ===== SECTION 2 ===== */
        .section-two {
          position: relative;
          width: 100%;
          height: 340px;
          margin-top: 10px;
        }

        .left-info {
          position: absolute;
          left: 60px;
          top: 0;
          line-height: 1.35;
          width: 330px;
        }

        .left-info strong {
          font-weight: 700;
          font-family: 'Archivo Narrow', sans-serif;
        }

        .left-info .sub {
          font-size: 10.5px;
          color: #666;
        }

        .right-info {
          position: absolute;
          right: 60px;
          top: 0;
          width: 480px;
        }

        .right-info h2 {
          font-size: 17px;
          font-weight: 700;
          margin: 0;
          padding: 0;
          font-family: 'Archivo Narrow', sans-serif;
          color: #2c3e50;
        }

        .invoice-date {
          position: absolute;
          right: 0;
          top: 5px;
          font-size: 11.5px;
          color: #666;
        }

        .info-table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 30px;
          font-size: 11px;
        }

        .info-table td {
          padding: 4px 5px;
          vertical-align: top;
        }

        .info-table td:first-child {
          font-weight: 700;
          font-family: 'Archivo Narrow', sans-serif;
        }

        .info-table tr {
          border-bottom: 1px solid #e0e0e0;
        }

        /* ===== SECTION 3 ===== */
        .section-three {
          position: relative;
          width: 100%;
          padding: 0 60px;
          margin-top: 15px;
          line-height: 1.35;
        }

        .section-three strong {
          font-weight: 700;
          font-family: 'Archivo Narrow', sans-serif;
        }

        .regulator {
          margin-bottom: 10px;
          padding: 8px;
          background-color: #f9f9f9;
          border-left: 3px solid #2c3e50;
        }

        .remit {
          margin-bottom: 15px;
          padding: 8px;
          background-color: #f9f9f9;
          border-left: 3px solid #3498db;
        }

        .remit p {
          margin: 3px 0;
        }

        table {
          width: 100%;
          border-collapse: collapse;
          font-size: 11px;
          margin-top: 10px;
        }

        th {
          text-align: left;
          font-weight: 700;
          border-bottom: 2px solid #2c3e50;
          padding-bottom: 4px;
          font-family: 'Archivo Narrow', sans-serif;
        }

        td {
          vertical-align: top;
          padding: 4px 0;
        }

        .qty, .price, .total {
          text-align: right;
        }

        tr.line td {
          border-bottom: 1px solid #e0e0e0;
        }

        .totals {
          width: 260px;
          margin-left: auto;
          margin-top: 10px;
          font-size: 11px;
          border-top: 2px solid #2c3e50;
        }

        .totals td {
          padding: 2px 0;
        }

        .bold {
          font-weight: 700;
          font-family: 'Archivo Narrow', sans-serif;
        }

        /* ===== SECTION 4 ===== */
        .section-four {
          position: relative;
          width: 100%;
          padding: 0 60px;
          margin-top: 25px;
        }

        .section-four strong {
          font-weight: 700;
          font-family: 'Archivo Narrow', sans-serif;
        }

        .terms {
          margin-bottom: 15px;
          padding: 8px;
          background-color: #f9f9f9;
          border-left: 3px solid #e74c3c;
        }

        .terms p {
          margin: 2px 0;
        }

        .info-grid {
          display: grid;
          grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
          margin-top: 15px;
          font-size: 11px;
          gap: 10px;
        }

        .info-grid div {
          line-height: 1.4;
          padding: 8px;
          background-color: #f9f9f9;
          border-radius: 4px;
        }

        .bank-info {
          margin-top: 40px;
          font-size: 10.5px;
          line-height: 1.4;
        }

        .bank-columns {
          display: grid;
          grid-template-columns: 1.3fr 1.3fr 1fr 1fr;
          gap: 40px;
        }

        .bank-columns > div {
          padding: 10px;
          background-color: #f9f9f9;
          border-radius: 4px;
        }

        .footer-bottom {
          position: absolute;
          bottom: 25px;
          right: 60px;
          font-size: 11px;
          background-color: #2c3e50;
          color: white;
          padding: 5px 15px;
          border-radius: 4px;
        }

        /* تحسينات إضافية */
        .highlight {
          background-color: #fffde7;
          padding: 2px 4px;
          border-radius: 2px;
        }

        .currency {
          font-weight: 700;
          color: #2c3e50;
        }

        sup {
          font-size: 8px;
          vertical-align: super;
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <div class="header-left">
        <strong>IKA<sup>®</sup> Works, Inc.</strong><br>
        2635 Northchase Pkwy SE<br>
        28405, Wilmington, NC<br>
        phone:+1 910 452 7059, fax: +1 910 452 7693<br>
        e-Mail: orders@ika.net, website: www.ika.com
    </div>
    <div class="header-right">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/IKA_Logo.svg/512px-IKA_Logo.svg.png" alt="IKA Logo">
    </div>
</div>

<!-- ===== SECTION 2 ===== -->
<div class="section-two">
    <div class="left-info">
        <div class="sub">IKA<sup>®</sup> Works, Inc. · 28405 Wilmington, NC</div>
        <br>
        <strong>RAMO TRADING & CONSULTING INC</strong><br>
        8 FAIR VALLEY<br>
        COTO DE CASA CA 92679
        <br><br>
        <strong>Attn.</strong>&nbsp;&nbsp;FINANCE DEPARTMENT, tel.: +1 833-669-0944
    </div>

    <div class="right-info">
        <h2>Invoice</h2>
        <div class="invoice-date">09/24/2025</div>

        <table class="info-table">
            <tr>
                <td>Invoice No.</td><td>2000536318</td>
                <td>Order No.</td><td>203325750</td>
                <td>Customer No.</td><td>10044222</td>
            </tr>
            <tr>
                <td>Your Order No.</td><td colspan="5">IK-414   09/23/2025</td>
            </tr>
            <tr>
                <td>Person In Charge</td><td colspan="2">Customer Service<br><strong>E-mail:</strong> Orders@ika.net</td>
                <td><strong>Phone:</strong></td><td colspan="2">+1 910 452 7059  505</td>
            </tr>
            <tr>
                <td>Dispatch Type</td><td>FedEx Ground</td>
                <td>Forwarder</td><td colspan="3">FEDEX FREIGHT</td>
            </tr>
            <tr>
                <td>Delivery Address</td>
                <td colspan="2">
                    MIRELA STANCIU<br>
                    FINANCE DEPARTMENT<br>
                    24106 E OLIVE LN<br>
                    99019 LIBERTY LAKE<br>
                    USA
                </td>
                <td>Delivery Conditions</td>
                <td colspan="2">FCA Wilmington<br><br><strong>Dispatch Date:</strong><br>09/24/2025</td>
            </tr>
        </table>
    </div>
</div>

<!-- ===== SECTION 3 ===== -->
<div class="section-three">
    <div class="regulator">
        <strong>Regulator:</strong><br>
        RAMO TRADING & CONSULTING INC<br>
        8 FAIR VALLEY<br>
        COTO DE CASA CA 92679
    </div>

    <div class="remit">
        <strong>Remit to:</strong><br>
        IKA<sup>®</sup> Works, Inc.; PO box 890161; 28289-0161 Charlotte, NC<br><br>
        If you have questions please call the above mentioned telephone number /e-mail address of our customer center.
        Please also feel free to contact the responsible person who is mentioned in your order confirmation.
    </div>

    <table>
        <thead>
        <tr>
            <th style="width:8%;">Item</th>
            <th style="width:20%;">Material No.</th>
            <th>Description</th>
            <th style="width:10%;" class="qty">Quantity</th>
            <th style="width:10%;" class="price">Price</th>
            <th style="width:15%;" class="total">Amount <span class="currency">USD</span></th>
        </tr>
        </thead>
        <tbody>
        <tr class="line">
            <td>10</td>
            <td>0003378000</td>
            <td>
                ETS-D5<br>
                <span style="font-size:10px; color: #666;">
            Electronic contact thermometer<br>
            Weight:&nbsp;&nbsp;&nbsp;&nbsp;0.455&nbsp;KG<br>
            Price Per Unit:&nbsp;&nbsp;1&nbsp;PC
          </span>
            </td>
            <td class="qty">1</td>
            <td class="price">257.40</td>
            <td class="total">257.40</td>
        </tr>

        <tr class="line">
            <td>20</td>
            <td>LFREIGHT</td>
            <td>Freight charges</td>
            <td class="qty">1</td>
            <td class="price">24.42</td>
            <td class="total">24.42</td>
        </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Net</td><td style="text-align:right;">281.82</td></tr>
        <tr><td>output tax domestic</td><td style="text-align:right;">0.00</td></tr>
        <tr class="bold"><td>Total Amount <span class="currency">USD</span></td><td style="text-align:right;">281.82</td></tr>
    </table>
</div>

<!-- ===== SECTION 4 ===== -->
<div class="section-four">

    <div class="terms">
        <strong>Terms Of Payment:</strong><br>
        advance payment<br><br>
        Please transfer the invoice value with considering of payment terms to one of our below mentioned bank accounts.<br>
        Please take care to indicate the correct invoice number on your payment.
    </div>

    <div class="info-grid">
        <div>
            <strong>Package</strong><br>
            <span class="highlight">1000531778</span>
        </div>
        <div>
            <strong>Delivery</strong><br>
            <span class="highlight">80425994</span>
        </div>
        <div>
            <strong>Net Weight</strong><br>
            <span class="highlight">0.455 KG</span>
        </div>
        <div>
            <strong>Gross Weight</strong><br>
            <span class="highlight">1.100 KG</span>
        </div>
        <div>
            <strong>Dimensions (l x w x h)</strong><br>
            <span class="highlight">16 x 12 x 8 ('')</span>
        </div>
    </div>

    <div class="bank-info">
        <div class="bank-columns">
            <div>
                <strong>USD - account:</strong><br>
                Truist bank<br>
                Routing Number/ABA Nr.: 053101121<br>
                Account Number: 5113721725<br>
                SWIFT: BRBT US33
            </div>

            <div>
                <strong>other currency - account:</strong><br>
                Truist bank<br>
                Account Number: 9088010074<br>
                SWIFT: SNTRUS3AXXX
            </div>

            <div>
                <strong>Managing director:</strong><br>
                Sarah Stiegelmann
            </div>

            <div>
                <strong>EIN:</strong> 31-1146959
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        Page <strong>1</strong> &nbsp; of &nbsp; <strong>2</strong>
    </div>

</div>

</body>
</html>
