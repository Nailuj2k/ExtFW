<?php

class delta
{

  function __construct($html='')
  {
    $this->html = $html;
    $this->attributes = array('bold'        => false,
                              // 'italic'      => false,
                              // 'underscore'  => false,
                              );
    $this->offset = 0;
  }

  function diff($json='')
  {
    if (count($json)) {
      $diff = array();
      foreach ($json as $entry) {
        foreach ($entry as $action => $value) {
          $diff[$action] = $value;
        }
      }
      if ($diff['insert']) {
        if ($diff['retain']) {
          if (count($this->attributes)) {
            foreach ($this->attributes as $attribute => $value) {
              if ($value == true && $diff['attributes'][$attribute] == false) {
                $span = '</span>';
                $this->html = substr_replace($this->html, $span, $diff['retain'] + $this->offset, 0);
                $this->offset = $this->offset + strlen('</span>');
                $this->attributes[$attribute] = false;
              } elseif ($value == false && $diff['attributes'][$attribute] == true) {
                $span = '<span style="font-weight: bold;">';
                $this->html = substr_replace($this->html, $span, $diff['retain'] + $this->offset, 0);
                $this->offset = $this->offset + strlen('<span style="font-weight: bold;">');
                $this->attributes[$attribute] = true;
              }

              if ($diff['insert']) {
                $this->html = substr_replace($this->html, $diff['insert'], $diff['retain'] + $this->offset, 0);
              }
            }
          }
        } else {
          $this->html = $diff['insert'];
        }
      }
      if ($diff['delete']) {
        if ($diff['retain']) {
          $this->html = substr_replace($this->html, '', $diff['retain'] + $this->offset, $diff['delete']);
        } else {
          $this->html = substr_replace($this->html, '', 0, $diff['delete']);
        }
      }
    }
  }

  function render()
  {
    return $this->html;
  }
}
