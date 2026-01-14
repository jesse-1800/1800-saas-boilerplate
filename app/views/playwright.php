<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap">
    <link rel="stylesheet" href="/dist/css/App.css?ver=<?=time()?>">
    <style>
        <?=$proposal->template_css?>
        <?=$proposal->paperwork_css?>
        .pdf-page {
            width: 8.5in !important;
            height: 11in !important;
            page-break-after: always !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            box-shadow: none !important;
        }
        html, body {
            overflow-y: unset;
            font-family: Outfit, Roboto, Helvetica, sans-serif;
        }
    </style>
    <script>
        window.onload = () => {
            const pdf_style = document.createElement('style');
            const contracts_displayed = <?=$proposal->show_contract_pages?>==1;
            const contract_pages = <?=json_encode($proposal->contract_pages)?>;
            const proposal_status = "<?=$proposal->status?>";

            // Hide regular & contract pages if specified in proposal settings
            if (!contracts_displayed) {
                pdf_style.innerHTML += '.contract-page{display: none !important}';
            }

            // Check each page display properties
            contract_pages.forEach(page => {
                // Hide if display is false
                if (page.display == false) {
                    pdf_style.innerHTML += `#${page.id}{display: none !important}`;
                }
                // Also hide if proposal is signed AND hide_if_signed is true
                else if (proposal_status == 'accepted' && page.hide_if_signed) {
                    pdf_style.innerHTML += `#${page.id}{display: none !important}`;
                }
            });

            // Attach element tp body
            document.head.appendChild(pdf_style);
        }
    </script>
</head>
<body id="view-proposal">
<?=$proposal->template_html?>
</body>
</html>