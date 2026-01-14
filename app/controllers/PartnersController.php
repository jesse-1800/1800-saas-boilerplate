<?php

use App\Models\IDProviderPartners;
use Kernel\Security\Encryption;

/**
 * Default controller
 * Class HomeController
 */
class PartnersController
{
    public function fetch()
    {
        (new Auth)->isLoggedIn();
        $partners = (new IDProviderPartners)::fetch();
        return json($partners);
    }

    public function store()
    {
        (new Auth)->isLoggedIn();
        $form = json_decode($_POST['partner']);
        $form->smtp_password = Encryption::encode($form->smtp_password);
        $form->supported_brands = json_encode($form->supported_brands);
        $form->is_active  = 1;
        $form->created_at = date('Y-m-d');
        $form->updated_at = date('Y-m-d');
        echo json_encode(
            ['result' => (new IDProviderPartners)::insert($form)]
        );
    }

    public function update()
    {
        (new Auth)->isLoggedIn();
        $Partners = new IDProviderPartners;
        $form = json_decode($_POST['partner']);
        $form->smtp_password = Encryption::encode($form->smtp_password);
        $form->supported_brands = json_encode($form->supported_brands);
        $form->updated_at = date('Y-m-d');
        $result = $Partners::update($form,$form->id);
        json(['result' => $result,'message' => $Partners::errors()]);
    }
}
