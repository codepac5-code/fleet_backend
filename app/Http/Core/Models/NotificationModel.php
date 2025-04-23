<?php
namespace App\Http\Core\Models;



class NotificationModel {

    public function __construct(
    private string $title_ar , private string $body_ar ,
    private string $title_en, private string $body_en ,
    private $image, private $onClick = false , private $type = null)
    {
         
    }
    public function get_title_by_locale_language()
    {
        return $this->{'title_'.app()->getLocale()};
    }
    
    public function get_body_by_locale_language()
    {
        return $this->{'body_'.app()->getLocale()};
    }

        /**
     * Get the value of title
     */ 
    public function get_title_en()
    {
        return $this->title_en;
    }

    /**
     * Get the value of title
     */ 
    public function get_title_ar()
    {
        return $this->title_ar;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */ 
    public function set_title_ar($title)
    {
        $this->title_ar = $title;

        return $this;
    }

        /**
     * Get the value of body
     */ 
    public function get_body_en()
    {
        return $this->body_en;
    }

    /**
     * Get the value of body
     */ 
    public function get_body_ar()
    {
        return $this->body_ar;
    }



    /**
     * Get the value of onClick
     */ 
    public function getOnClick()
    {
        return $this->onClick;
    }

    /**
     * Set the value of onClick
     *
     * @return  self
     */ 
    public function setOnClick($onClick)
    {
        $this->onClick = $onClick;

        return $this;
    }

    /**
     * Get the value of image
     */ 
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */ 
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}