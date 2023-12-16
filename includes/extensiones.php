<?php
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\TwigTag;

class Extensiones extends AbstractExtension {

    public static function trim_text($input, $length) {
        return misc::trim_text($input, $length);
    }

    public static function url_sortable($campo="id", $direccion="desc") {
        return misc::url_sortable($campo, $direccion);
    }
    public static function format_number($numero){
        return misc::number_format($numero);
    }
    public static function format_date($fecha){
        return misc::date_format($fecha);
    }
    public static function formato_periodo($id_factura) {
        return Misc::factura_a_periodo($id_factura);
    }

    public function getFunctions() {
        return [
            'format_date'=> new TwigFunction('format_date', [$this, 'format_date']),
            'format_number'=> new TwigFunction('format_number', [$this,'format_number']),
            'url_sortable' => new TwigFunction('url_sortable', [$this, 'url_sortable']),
            'trim_text' => new TwigFunction('trim_text', [$this, 'trim_text']),
            'formato_periodo' => new TwigFunction('formato_periodo', [$this,'formato_periodo'])
        ];
    }

}