<?php declare(strict_types=1);

namespace SUP\PDF;

use SUP\PDF\Template;
use SUP\User;
use Slim\Http\Response;

class Generate
{
    
    protected $container;
    protected $pdf;
    protected $supVersion;
    protected $user;

    protected $title;
    protected $version;

    public function __construct(\Slim\Container $container, string $title, string $version, $callable = Template::class)
    {
        $this->container = $container;
        $this->title = $title;
        $this->version = $version;

        $this->supVersion = $this->container->settings['public']['version'];

        $this->pdf = new $callable(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $this->pdf->SetCreator($this->container->base->lang->g('generated-by', 'pdf') . ' SUPi');
        $this->pdf->SetAuthor('SUPi v' . $this->supVersion);
        $this->pdf->SetTitle('SUPi - ' . $this->title . ' ' . $this->version);
        $this->pdf->SetSubject($this->title);

        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $this->pdf->SetMargins(PDF_MARGIN_LEFT, 25+16, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->pdf->SetAutoPageBreak(true, 60);
    }

    public function setContent(string $code, string $longCode, string $uri, User $user)
    {
        $this->user = $user;

        $this->pdf->setContent([
            'supVersion' => $this->supVersion,
            'title' => $this->title,
            'version' => $this->version,
            'page' => $this->container->base->lang->g('page', 'pdf'),
            'code' => $code,
            'longCode' => $longCode,
            'uri' => $uri
        ]);
    }

    public function setData(array $headers, array $data, array $sizing)
    {
        $this->data = [$headers, $data, $sizing];
    }

    public function generate(Response &$response)
    {
        $this->pdf->AddPage();
        
        $this->pdf->SetFont('dejavusans', '', 11);
        $userTable = <<<TEXT
<style>
.top td {
    height: 25px;
}
</style>
<table>
    <tr class="top"><td width="70">{$this->container->base->lang->g('name', 'pdf')}</td><td><b>{$this->user->getAnyName()}</b></td></tr>
    <tr><td width="70">{$this->container->base->lang->g('class', 'pdf')}</td><td><b>{$this->user->getAttribute('class')}</b></td></tr>
</table>
TEXT;
        $this->pdf->writeHTML($userTable, true, false, false, false, '');
        
        $this->pdf->SetY($this->pdf->GetY() + 5);
        $this->pdf->SetFont('dejavusans', '', 12);

        $this->pdf->writeTable($this->data[0], $this->data[1], $this->data[2]);

        $this->pdf->lastPage();
        $this->pdf->SetAutoPageBreak(false);

        $style = [
            'width' => 0.2,
            'color' => [0, 0, 0]
        ];
        $this->pdf->Line(20, 249, 100, 249, $style);
        $this->pdf->setY(250);
        $this->pdf->setX(20);
        $this->pdf->SetFont('dejavusans', 'I', 10);
        $this->pdf->Cell(80, 0, $this->container->base->lang->g('signature', 'pdf'), false, 0, 'C');

        $response->getBody()->write($this->pdf->Output('SUPi-' . $this->title . '.pdf', 'S'));

        return $response->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Cache-Control', 'private, must-revalidate, post-check=0, pre-check=0, max-age=1')
            ->withHeader('Pragma', 'public')
            ->withHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->withHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->withHeader('Content-Disposition', 'inline; filename="SUPi-' . $this->title . '.pdf"');
    }
}