<?php

/**
 * Default controller
 * Class HomeController
 */
class HomeController
{
    /**
     * @return View
     */
    public function index()
    {
        return View::render(SYSTEM_MODE.TEMPLATE_TYPE);
    }
}
