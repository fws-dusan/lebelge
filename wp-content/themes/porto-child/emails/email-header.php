<?php

declare(strict_types=1);

// prepare variables
$BlogName = get_bloginfo('name', 'display');
$AssetsURL = get_stylesheet_directory_uri();

// prepare variables
$data = get_query_var('Data');
$headerCaption = $data['headerCaption'] ?? 'Someone reached out!';

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title>
    </title>
    <!--[if !mso]>-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            display: block;
            margin: 13px 0;
        }
    </style>
    <!--[if mso]>
    <xml>
    <o:OfficeDocumentSettings>
        <o:AllowPNG/>
        <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <!--[if lte mso 11]>
    <style type="text/css">
    .mj-outlook-group-fix { width:100% !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        @media only screen and (min-width:480px) {
            .mj-column-per-100 {
                width: 100% !important;
                max-width: 100%;
            }

            .mj-column-per-50 {
                width: 50% !important;
                max-width: 50%;
            }

            .mj-column-per-25 {
                width: 25% !important;
                max-width: 25%;
            }
        }
    </style>
    <style type="text/css">
        @media only screen and (max-width:480px) {
            table.mj-full-width-mobile {
                width: 100% !important;
            }

            td.mj-full-width-mobile {
                width: auto !important;
            }

            .table-code-th {
                padding-left: 16px !important;
            }

            .table-code-th span {
                font-size: 27px !important;
            }

            .table-code-th strong {
                margin-top: 16px !important;
                padding: 10px !important;
            }
        }
    </style>
    <style type="text/css">
        .box-shadow {
            box-shadow: 0px 6px 40px -1px rgba(88, 0, 111, 0.06);
        }

        @media (max-width: 575px) {
            .mobile {
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>
    <div style="">
        <!--[if mso | IE]>
        <table
                align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600"
        >
        <tr>
            <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                <v:rect  style="width:600px;" xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false">
                    <v:fill  origin="0, -0.5" position="0, -0.5" src="<?= esc_url($AssetsURL) ?>images/header-email-background.png" type="frame" size="1,1" aspect="atleast" />
                    <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
        <![endif]-->
        <div style="margin:0px auto;max-width:600px;">
            <div style="line-height:0;font-size:0;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                    <tbody>
                        <tr>
                            <td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:40px;padding-left:0px;padding-right:0px;padding-top:30px;text-align:center;">
                                <!--[if mso | IE]>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td
                                        class="" style="vertical-align:top;width:600px;"
                                >
                            <![endif]-->
                                <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width:200px;">
                                                                <a href="<?= esc_url(home_url()) ?>" target="_blank" rel="noopener">
                                                                    <img height="auto" src="<?= esc_url($AssetsURL) ?>/logo.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="200" />
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                            <td
                                    class="" style="vertical-align:top;width:600px;"
                            >
                            <![endif]-->
                                <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:10px 25px;padding-top:50px;padding-right:0px;padding-bottom:0;padding-left:0px;word-break:break-word;">
                                                <div style="font-family:OpenSans, Arial, sans-serif;font-size:30px;line-height:1;text-align:center;color:#000000;"><span style="font-size:30px; font-weight:bold; color: #A86133;">
                                                        <?php echo esc_html($headerCaption); ?>
                                                    </span></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:0px;padding:10px 25px;padding-top:10px;padding-right:0;padding-bottom:0px;padding-left:0;word-break:break-word;">
                                                <p style="border-top:solid 1px rgba(255, 255, 255, 0.7);font-size:1px;margin:0px auto;width:300px;">
                                                </p>
                                                <!--[if mso | IE]>
                            <table
                               align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 1px rgba(255, 255, 255, 0.7);font-size:1px;margin:0px auto;width:300px;" role="presentation" width="300px"
                            >
                              <tr>
                                <td style="height:0;line-height:0;">
                                  &nbsp;
                                </td>
                              </tr>
                            </table>
                          <![endif]-->
                                            </td>
                                        </tr>
                                        <!-- <tr>
                                            <td align="center" style="font-size:0px;padding:10px 25px;padding-top:10px;padding-right:0px;padding-bottom:0;padding-left:0px;word-break:break-word;">
                                                <div style="font-family:OpenSans, Arial, sans-serif;font-size:20px;line-height:1;text-align:center;color:#000000;"><span style="font-size:20px; font-weight:normal; color: #494949;">Please see info below.</span></div>
                                            </td>
                                        </tr> -->
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                            </tr>
                            </table>
                            <![endif]-->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!--[if mso | IE]>
        </v:textbox>
        </v:rect>
        </td>
        </tr>
        </table>
        <table
                align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600"
        >
        <tr>
            <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
        <![endif]-->