<?php
/**
 *  DB - A simple database class 
 * 
 * @author		Author: Mohamed Nabil. (https://facebook.com/mohamed.nabil.3762)
 *        
 * 
 *
 */
namespace app\Database{
use pdo; 
class DB
{
    # @object, The PDO object
    private $pdo;
    
    # @object, PDO statement object
    private $sQuery;
    

    
/**
     *   Default Constructor 
     
     *	1. Connect to database.
    
     */
    public function __construct()
    {
        
        $this->Connect();
       
    }
    
/**
     *	This method makes connection to the database.
     *	
     *	1. Reads the database settings from a config.php file. 
     *	2. Puts  the ini content into the settings array.
     *	3. Tries to connect to the database.
     *	4. If connection failed, exception is displayed 
     */
    private function Connect()
    {
      
        $dsn            =DB.':host='.HOSTNAME.';dbname='.DBNAME;
        try {
          
            $this->pdo = new PDO($dsn,USERNAME,PASSWORD, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ));
            
            # We can now log any exceptions on Fatal error. 
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            # Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            
        
        }
        catch (PDOException $e) {
            
                echo '<h1>Error:</h1>'.$e->getMessage();
            die();
        }
    }
     
/**
     *execute any Data from database  
     *@param $query string  //for sql statment
     *@param $data  array  // for prepared data
     *@return bolean
     */
    public function pdoSql($query,$data='')
    {
        //check data array
         if (!is_array($data)) {
            $data=array();
         }

       //prepare query 
     $prepare=$this->pdo->prepare($query);
         
         $this->sQuery=$prepare;
      
      $doquery=$prepare->execute($data);
       //echo $query;
         
       return true;
   
        return false;
    






       }
    
/**
     * Inserting row into database
     * @param string $table
     * @param array $data
     * @return boolean
     */

    public function insert($table,$data)
     {
         
          // setup some variables for fields and values
        $fields  = '';
        $values = '';
        $data2=array(); // data paramters
        // populate them
        //if (is_array($data)) {
       // echo "1";
       // }
      
        foreach ($data as $f => $v)
        {
            $fields  .= "`$f`,";
            $values .= "?,";
            $data2[count($data2)]=$v;
        }

        // remove our trailing ','
        $fields = substr($fields, 0, -1);
        // remove our trailing ','
        $values = substr($values, 0, -1);
        
        $querystring = "INSERT INTO `{$table}` ({$fields}) VALUES({$values})";
               
        //echo $querystring;
        // print_r($data2);
       


        if($this->pdoSql($querystring,$data2))
            
         return TRUE;

        return FALSE;




     }



 /**
     * Delete row in database
     * @param string $from
     * @param array  $where
     * @return boolean
     */
    public function Delete($from,$where='')
    {      

        $whereq='';     // WHERE query
        $whereword='';  // add 'WHERE' if where query exists
        $data=array(); // for exexcute query none ':'
          if (is_array($where))
        {
            $whereword.='WHERE';
          foreach ($where as $k => $val)
          {
            $whereq.="$k"."=?".' ';
            $data[count($data)]="$val";
        }


        }



         $query ="DELETE FROM `$from`.' '.$whereword.' '.$whereq";
         //echo $query;

         $result = $this->pdoSql($query,$data);
         if($result && $this->sQuery->rowCount>0)
             return TRUE;
         
         return FALSE;
    }

/**  
     * Update row in database
     * @param string $table
     * @param array  $where
     * @param array  $data
     * @return boolean
     */

    public function Update($table,$data,$where='')
    {
        //set $key = $value :)
        
        $query  = '';
        $data2=array(); // for exexcute query none ':'
        $whereq='';     // WHERE query
        $whereword='';  // add 'WHERE' if where query exists
        $data3=array(); // for exexcute query none ':'
          if (is_array($where))
        {
            $whereword.='WHERE';
          foreach ($where as $k => $val) {
            $whereq.="$k"."=?".' ';
            $data3[count($data3)]=$val;
        }


        }


        foreach ($data as $f => $v) {
           
            $query  .= "`$f` = ? ,";
            $data2[count($data2)]="$v";
        }
        
        //Remove trailing ,
        $query = substr($query, 0,-1);
        
        $querystring = "UPDATE `{$table}` SET {$query} {$whereword} {$whereq}";
       // echo $querystring;
        $fullex=array_merge($data2,$data3);

       if($this->pdoSql($querystring,$fullex))
            
         return TRUE;

        return FALSE;

    }


/**
  *get rows from last query
  *@param string $option;
  *@return data result
  */
    public function getRows($option='',$classname='')
    {

       switch ($option)
       {
           case 'PDO::FETCH_ASSOC':
               $option=PDO::FETCH_ASSOC;
               break;
           case 'PDO::FETCH_NUM':
           $option=PDO::FETCH_NUM;
               break;
           case 'PDO::FETCH_CLASS':
               $clasname=$classname;
               $option=PDO::FETCH_CLASS;
               break;
           default:
             $option=PDO::FETCH_ASSOC;
               break;
       }
       $result=array();
       $rows=$this->affectedRows();
       
       for ($i=0; $i <$rows; $i++)
       { 
          if ($classname != '')
          {
            $class_query_method=$this->sQuery->fetch($option,$classname);
           
          }
          else{
                 $fetch=$this->sQuery->fetch($option);
                 $result[]= $fetch;
              }       
       }
       
     
       If(count($result) > 0)
       {
             return $result;
            
      
       }
          return $fetch;
    }


//get one data row

    public function getRow($option='pdo::fetch_assoc')
    {
       
       switch ($option)
       {
           case 'PDO::FETCH_ASSOC':
               $option=PDO::FETCH_ASSOC;
               break;
           case 'PDO::FETCH_NUM':
           $option=PDO::FETCH_NUM;
               break;
           case 'PDO::FETCH_CLASS':
               $clasname=$classname;
               $option=PDO::FETCH_CLASS;
               $fetch=$this->sQuery->fetch($option,$classname);
               break;
           default:
             $option=PDO::FETCH_ASSOC;
               break;
       }
       $result=array();
       $rows=$this->affectedRows();
      
       for ($i=0; $i <$rows; $i++)
       { 
           $result[]= $fetch=$this->sQuery->fetch($option);
       }
       
     
       If(count($result) > 0)
       {
             return $result[0];
            
      
       }
          return NULL;



    }
    
// Count Query Affected Rows    
   
    public function affectedRows()
    {    
        if ($this->sQuery != NULL){
          return $this->sQuery->rowCount(); 
        }
       
    }

// function returns id for last inserted record 

    public function lastId()
    {
        return $this->pdo->lastInsertId();
    }
  }
}