<?php 

  class TableMysql extends Table{
    
    protected $db_instance;
    private $resource;

    function __construct($tablename){
      parent::__construct($tablename);
    }

    function __destruct(){
    }

    function select($params=false) {

      $sql   = 'SELECT '.$this->fields;
      $sql  .= ' FROM '.$this->tablename;
      if($this->where)   $sql  .= ' WHERE '.$this->where;
      if($this->orderby) $sql  .= ' ORDER BY '.$this->orderby;



      $_total = recordCount($this->tablename); //,$_sql_count_where);
      if( $_total > 0 ) {
        
        $_total_pages = ceil($_total/$this->page_num_items); 
        $_page_start = $this->page_num_items * $this->page - $this->page_num_items; 
        $sql .= "  LIMIT  $_page_start,{$this->page_num_items}";
        $s = self::sqlQuery($sql);
      
        while ($rows) {
          $this->addRow($row);
        }

        if($_total_pages>1){
         
         
          $_link_back=$_link_add=$_link_config=false;                    
          if($_total_pages>1) 
             $this->paginator = paginator(0, $_total, $this->page_num_items, $this->page, $this->paginator_link, 0, true, true, true); //, true, true, true );  
          else
             $this->paginator = false;
        }

      }else{
      
        echo '0';

      }


    }

  }

?>