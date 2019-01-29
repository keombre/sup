<?php declare(strict_types=1);

namespace SUP\PDF;

class Template extends \TCPDF
{
    protected $contentAPI;

    public function setContent($data) {
        $this->contentAPI = $data;
    }

    public function Header() {
        // dejavusansextralight
        // dejavusans
        // dejavusanscondensed

        $topText = '<span>' . date("d. m. Y") . ' – <b>SUPi</b> v' . $this->contentAPI['supVersion'] . '</span>';

        $header = $this->startTemplate(180, 25);

            $barcodeStyle = [
                'position' => '',
                'align' => 'R',
                'stretch' => false,
                'fitwidth' => false,
                'border' => false,
                'hpadding' => 0,
                'vpadding' => 0,
                'fgcolor' => [0,0,0],
                'bgcolor' => false
            ];

            $this->SetFont('dejavusansextralight', '', 20);
            $this->Cell(80, 11, $this->contentAPI['title'], false, 1, '', false, '', 0, false, 'T', 'B');

            $this->SetFont('dejavusans', 'B', 20);
            $this->Cell(0, 2, $this->contentAPI['version'], false, 1);

            $this->SetFont('dejavusans', '', 8);
            $this->writeHTMLCell(0, 0, 0, 0, $topText, 0, 0, false, true, 'R');

            $this->write1DBarcode($this->contentAPI['longCode'], 'C39E', 0, 4, 180, 7, 0.4, $barcodeStyle, 'L');

        $this->endTemplate();
        $this->printTemplate($header, 15, 14);
    }

    public function Footer() {

        $qrStyle = [
            'border' => 0,
            'vpadding' => 0,
            'hpadding' => 0,
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        ];
        $text = $this->contentAPI['page'] . ' ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
        $this->SetY(-25);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(150, 10, $text, false, 0, 'L');

        $this->SetY(-54);
        $this->SetX(165);

        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(30, 5, $this->contentAPI['code'], false, 0, 'C');
        $this->write2DBarcode($this->contentAPI['uri'], 'QRCODE,M', 165, 248, 45, 45, $qrStyle, '');
    }

    public function writeTable($header, $data, $sizing)
    {
        
        $tbl = <<<TEXT
<style>
.thead td {
    border-bottom: 1px solid black;
}
.tbody td {
    border-top: 1px solid #aaa;
}
table {
    padding: 0 0 0 10;
}
</style>
<table>
<thead><tr class="thead">
TEXT;
        foreach ($header as $id => $hentry) {
            $tbl .= '<td width="' . $sizing[$id] . '%"><b>' . $hentry . '</b></td>'.PHP_EOL;
        }
        $tbl .= '</tr></thead>'.PHP_EOL;
        foreach ($data as $line) {
            $tbl .= '<tr class="tbody" nobr="true">'.PHP_EOL;
            foreach ($line as $id => $entry) {
                $tbl .= '<td width="' . $sizing[$id] . '%">' . $entry . '</td>'.PHP_EOL;
            }
            $tbl .= '</tr>'.PHP_EOL;
        }
        $tbl .= '</table>'.PHP_EOL;

        $this->setCellHeightRatio(2.5);
        $this->SetFont('dejavusans', '', 11);
        $this->writeHTML($tbl, true, false, false, false, '');
    }
}