<?php

/**
 * Model database for Voting tables
 *
 * @package OSClass
 * @subpackage Model
 * @since 3.0
 */
class AR extends DAO
{
  /**
   * It references to self object: ModelVoting.
   * It is used as a singleton
   *
   * @access private
   * @since 3.0
   * @var ModelVoting
   */
  private static $instance ;

  /**
   * It creates a new ModelVoting object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since 3.0
   * @return ModelVoting
   */
  public static function newInstance()
  {
      if( !self::$instance instanceof self ) {
          self::$instance = new self ;
      }
      return self::$instance ;
  }

  /**
   * Construct
   */
  function __construct()
  {
      parent::__construct();
  }

  /**
   * Return table name voting item
   * @return string
   */
  public function getTable_Item()
  {
      return DB_TABLE_PREFIX.'t_rated_items';
  }

  /**
   * Return table name dev
   * @return string
   */
  public function getTable_dev()
  {
      return DB_TABLE_PREFIX.'t_dev';
  }

  /**
   * Return table name voting user
   * @return string
   */
  // public function getTable_User()
  // {
  //     return DB_TABLE_PREFIX.'t_voting_user';
  // }

  /**
   * Import sql file
   * @param type $file
   */
  public function import($file)
  {
      $path = osc_plugin_resource($file) ;
      $sql = file_get_contents($path);

      if(! $this->dao->importSQL($sql) ){
          throw new Exception( "Error importSQL::AR<br>".$file ) ;
      }
  }

  /**
   * Remove data and tables related to the plugin.
   */
  public function uninstall()
  {
      $this->dao->query("DELETE FROM ".DB_TABLE_PREFIX."t_plugin_category WHERE s_plugin_name = 'ads_rating'" );
      $this->dao->query("DROP TABLE ". $this->getTable_Item());
      $this->dao->query("DROP TABLE ". $this->getTable_dev());
      
  }


  // update
  public function _update($table, $values, $where)
  {
      $this->dao->from($table) ;
      $this->dao->set($values) ;
      $this->dao->where($where) ;
      return $this->dao->update() ;
  }


  // item related --------------------------------------------------------

  /**
   * Insert Item rating
   *
   * @param type $itemId
   * @param type $userId
   * @param type $iVote
   * @param type $hash
   * @return type
   */
  function insertItemVote($itemId, $userId, $iVote, $hash)
  {
      $aSet = array(

          'fk_i_item_id' => (int)$itemId,
          'i_value'      => (int)$iVote,
          's_hash'       => is_null($hash) ? "" : "$hash"

      );

      if($userId != 'NULL' && is_numeric($userId) ) {

          $aSet['fk_i_user_id']  = $userId;

      }

      return $this->dao->insert($this->getTable_Item(), $aSet);
  }

  /**
   * Return an average of ratings given an item id
   *
   * @param type $id
   * @return type
   */
  function getItemAvgRating($id) {

    if(is_numeric($id)) {

      $this->dao->select('format(avg(i_value),1) as vote');
      $this->dao->from( $this->getTable_Item());
      $this->dao->where('fk_i_item_id', (int)$id );

      $result = $this->dao->get();

      if(! $result ) {

        return array() ;

      }

      return $result->row();

    } else {

      return array('vote' => 0);

    }

  }

  /**
   * Return the number of votes given an item id
   *
   * @param type $id
   * @return type
   */
  function getItemNumberOfVotes($id) {

    if(is_numeric($id)) {

      $this->dao->select('count(*) as total');
      $this->dao->from( $this->getTable_Item());
      $this->dao->where('fk_i_item_id', (int)$id );

      $result = $this->dao->get();
      
      if(! $result ) {
          return array() ;
      }

      return $result->row();
  
    } else {
  
      return array('total' => 0);

    }

  }

  /**
   * Return rating given an item id and hash
   *
   * @param type $itemId
   * @param type $hash
   * @return type
   */
  function getItemIsRated($itemId, $hash, $userId = null)
  {

    if( is_numeric($itemId) && ( $userId == null || is_numeric($userId) ) ) {

      $this->dao->select('i_value');
      $this->dao->from( $this->getTable_Item());
      $this->dao->where('fk_i_item_id', (int)$itemId );

      if( $userId == null) {

        $this->dao->where('fk_i_user_id IS NULL');

      } else {

        $this->dao->where('fk_i_user_id', (int)$userId);
      }

      $this->dao->where('s_hash'      , (string)$hash );

      $result = $this->dao->get();

      if(! $result ) {

        return array() ;

      }

      return $result->row();

    } else {

      return array();

    }

  }


  /**
   * Return an array of items ordered by avg_votes
   *
   * @param type $category_id
   * @param type $order
   * @param type $num
   * @return type
   */
  
  function getItemRatings($category_id = null, $order = 'desc', $num = 5)
  {
      $sql  = 'SELECT fk_i_item_id as item_id, format(avg(i_value),1) as avg_vote, count(*) as num_votes, '.DB_TABLE_PREFIX.'t_item.fk_i_category_id as category_id ';
      
      if(!is_null($category_id)) {
          $sql .= ', '.DB_TABLE_PREFIX.'t_category.fk_i_parent_id as parent_category_id ';
      }
      
      $sql .= 'FROM '.DB_TABLE_PREFIX.'t_rated_items ';
      $sql .= 'LEFT JOIN '.DB_TABLE_PREFIX.'t_item ON '.DB_TABLE_PREFIX.'t_item.pk_i_id = '.DB_TABLE_PREFIX.'t_rated_items.fk_i_item_id ';
      $sql .= 'LEFT JOIN '.DB_TABLE_PREFIX.'t_category ON '.DB_TABLE_PREFIX.'t_category.pk_i_id = '.DB_TABLE_PREFIX.'t_item.fk_i_category_id ';
      
      if(!is_null($category_id)) {
          $sql .= 'WHERE '.DB_TABLE_PREFIX.'t_item.fk_i_category_id = '.$category_id.' ';
          $sql .= 'OR '.DB_TABLE_PREFIX.'t_category.fk_i_parent_id = '.$category_id.' ';
          $sql .= ' AND ';
      }else{
          $sql .= 'WHERE ';
      }
      
      $sql .= ''.DB_TABLE_PREFIX.'t_item.b_active = 1 ';
      $sql .= 'AND '.DB_TABLE_PREFIX.'t_item.b_enabled = 1 ';
      $sql .= 'AND '.DB_TABLE_PREFIX.'t_item.b_spam = 0 ';
      $sql .= 'AND ('.DB_TABLE_PREFIX.'t_item.b_premium = 1 || '.DB_TABLE_PREFIX.'t_category.i_expiration_days = 0 ||DATEDIFF(\''.date('Y-m-d H:i:s').'\','.DB_TABLE_PREFIX.'t_item.dt_pub_date) < '.DB_TABLE_PREFIX.'t_category.i_expiration_days) ';
      $sql .= 'AND '.DB_TABLE_PREFIX.'t_category.b_enabled = 1 ';
      $sql .= 'GROUP BY item_id ORDER BY avg_vote '.$order.', num_votes '.$order.' LIMIT 0, '.$num;

      $result = $this->dao->query($sql);
      if( !$result ) {
          return array() ;
      }

      return $result->result();
  }

  // Get recommended ads per user based on his rated items.
  function get_predict_best($userId, $limit) {

    $sql  = "SELECT d.i_item_id_1 as item, ";
    $sql .= "sum( d.i_sum + d.i_count * r.i_value) / sum(d.i_count) as avgrat ";
    $sql .= "FROM " . DB_TABLE_PREFIX . "t_item i, " . DB_TABLE_PREFIX . "t_rated_items r, " . DB_TABLE_PREFIX . "t_dev d ";
    $sql .= "WHERE r.fk_i_user_id=" . $userId . " AND d.i_item_id_1 <> r.fk_i_item_id ";
    $sql .= "GROUP BY d.i_item_id_1 ";
    $sql .= "ORDER BY avgrat DESC ";
    $sql .= "LIMIT " . $limit;

    $result = $this->dao->query($sql);

    if(! $result) {

      return array() ;
    }

    return $result->result();

  }


  function update_dev_table($userId, $itemId) {
    
    $sql  = "SELECT DISTINCT r.fk_i_item_id, (r2.i_value - r.i_value) as rating_difference";
    
    $sql .= " FROM " . DB_TABLE_PREFIX . "t_rated_items r, " . DB_TABLE_PREFIX . "t_rated_items r2";
    
    $sql .= " WHERE r.fk_i_user_id = " . $userId . " AND r2.fk_i_item_id = " . $itemId . " AND r2.fk_i_user_id = " . $userId; 

    $result = $this->dao->query($sql);

    if(! $result ) {

      return array() ;

    }


    //For every one of the user ’s rating pairs ,
    //update the dev table
    foreach ($result->result() as $row) {
      
      $other_item_id     = $row["fk_i_item_id"];

      $rating_difference = $row["rating_difference"];

      $this->dao->select("i_item_id_1");

      $this->dao->from( $this->getTable_dev() );
      
      $this->dao->where("i_item_id_1 = " . $itemId . "AND i_item_id_2 = " . $other_item_id );

      $r = $this->dao->get();

      if ( $r ) {

        $update_array = array('i_count' => 'i_count' + 1, 'i_sum' => 'i_sum'+ $rating_difference);

        $this->_update($this->getTable_dev(), $update_array, array('i_item_id_1' => $itemId, 'i_item_id_2' => $other_item_id));

        if ( $itemId != $other_item_id ) {

          $this->_update($this->getTable_dev(), array('i_count' => 'i_count' + 1, 'i_sum' => 'i_sum' - $rating_difference), array('i_item_id_1' => $other_item_id, 'i_item_id_2' => $itemId));

        }

      }

      else {

        $aSet = array(

          'i_item_id_1' => $itemId != $other_item_id ? (int)$other_item_id  : (int)$itemId,
          'i_item_id_2' => $itemId != $other_item_id ? (int)$itemId : (int)$other_item_id,
          'i_count' => 1,
          'i_sum' => $itemId != $other_item_id ? $rating_difference : - $rating_difference
        );

        $this->dao->insert($this->getTable_dev(), $aSet);

      }

    }
 
  }

  /**
   * Delete table entries related to an item id
   *
   * @param type $itemId
   * @return type
   */
  function deleteItem($itemId)
  {
      if(is_numeric($itemId)) {
          return $this->dao->delete($this->getTable_Item(), 'fk_i_item_id = '.$itemId);
      }
      return false;
  }

}
?>