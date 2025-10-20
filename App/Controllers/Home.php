<?php
namespace App\Controllers;

class Home extends Controller
{
    /**
     * Displaying the home page.
     * @return void
     */
    public function index(): void
    {
        $this->getData()["title"] = "Welcome to BlockBase CMS";
        $this->getData()["message"] = "Welcome to your PHP CMS 🚀";
        $this->view("Home.index");
    }

    /**
     * Displaying the about page.
     * @return void
     */
    public function about(): void
    {
        $this->getData()["title"] = "About BlockBase CMS";
        $this->getData()["message"] = "This is the About Page.";
        $this->view("Home.about");
    }

    /**
     * Displaying a contact page.
     * @return void
     */
    public function contact(): void
    {
        $this->getData()["title"] = "Contact Us";
        $this->getData()["message"] = "Get in touch with us.";
        $this->view("Home.contact");
    }
}
