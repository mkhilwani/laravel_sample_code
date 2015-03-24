<?php

class WineController extends BaseController
{

    /**
     * Check user loggedin or not via construct
     *
     * @return  winepost module
     * @date    23th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public function __construct()
    {
        $Controllername = Route:: currentRouteAction();
        $jsfile = explode("@", $Controllername);
        View::share('jsfile', $jsfile[0]);
    }

    /**
     * Display a details of wines on selected wine.
     *
     * @return wine details page($winedid)
     * @date    30th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    /*  public function details($id) {
          #is need to check with manish sir is removed module for desiging related
          $result = Product::SelectedProduct($id);
          $scale = Scale::select(DB::raw('concat (value,"_",scale) as value,scale'))->lists('scale', 'value');
          $mynote = array('');
          return View::make('frontend.wine.details', array('scale' => $scale, 'wines' => $result, 'mynote' => $mynote));
      }*/

    /**
     * insert mytasting note
     *
     * @return wine details page(all form data)
     * @date    30th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public function mytastingnote()
    {
        if (Input::has('is_flawed')) {
            $is_flawed = 1;
        } else {
            $is_flawed = 0;
        }
        if (Input::has('twitter_post')) {
            $twitter_post = 1;
        } else {
            $twitter_post = 0;
        }
        if (Input::has('facebook_post')) {
            $facebook_post = 1;
        } else {
            $facebook_post = 0;
        }
        if (Input::has('is_public')) {
            $is_public = 1;
        } else {
            $is_public = 0;
        }
        if (strpos(Input::get('scale'), 'point') !== false) {
            $score = Input::get('input_score');
        } else {
            $score = Input::get('star_score');
        }
        $tasting = new Tasting;
        $tasting->product_id = Input::get('product_id');
        $tasting->user_id = Auth::user()->id;
        $tasting->scale = Input::get('scale');
        $tasting->twitter_post = $twitter_post;
        $tasting->facebook_post = $facebook_post;
        $tasting->score = $score;
        $tasting->tasting_note = Input::get('tasting_note');
        $tasting->tasting_note_added_date = date('Y-m-d', strtotime(Input::get('tasting_note_added_date')));
        $tasting->drink_dates = Input::get('drink_dates');
        $tasting->tasting_tags = Input::get('tasting_tags');
        $tasting->is_flawed = $is_flawed;
        $tasting->is_public = $is_public;
        $tasting->created_at = date('Y-m-d h:i:s');
        $tasting->updated_at = date('Y-m-d h:i:s');
        $tasting->save();
        /* for insert in front need to test */
        echo "instid=" . $tasting->id;
    }

    /**
     * selected user winepost list
     *
     * @return $userid
     * @date   14th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public function GetSelectedUserWineposts($id)
    {

        if (!is_numeric($id)) {
            if ($id == 'all') {
                $orderby = 'id';
            } elseif ($id == 'mostviewd') {
                $orderby = 'view_count';
            } elseif ($id == 'recently') {
                $orderby = 'created_at';
            } else {
                $orderby = 'cancel';
            }
            if ($orderby != 'cancel') {
                #winepost list get
                $allwinepost = Winepost::AllWinepostListByOrder($orderby);
            } else {
                $allwinepost = null;
            }
        } else {
            #winepost list get
            $allwinepost = Winepost::UserWinepostList($id,12);
        }
        #right sidebar category list display
        $categorylist = WinePostCategory::GetCategoryCount();
        #mostviewd Wineposts list 3
        $mostviewdwinepost = Winepost::MostViewdWinepostList(3);
        #recent Wineposts list 3
        $mostrecentwinepost = Winepost::MostRecentWinepostList();
        return View::make('frontend.wine.userwineposts', array('wineposts' => $allwinepost, 'categorys' => $categorylist, 'mostrecentwineposts' => $mostrecentwinepost, 'mostviewdwineposts' => $mostviewdwinepost));
    }

    /**
     * selected  category winepost list
     *
     * @return $catId
     * @date   15th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public function GetSelectedCategoryWineposts()
    {
        $categoryId = Input::get("CatId");
        #winepost list get
        if ($categoryId == "mostviewd") {
            $categorywinepost = Winepost::MostViewdWinepostList();
            $categoryName = 'All';
        } else if ($categoryId == "all") {
            #all winepost list get
            $categorywinepost = Winepost::UserWinepostList(NULL, NULL);
            $categoryName = 'All';
        } else {
            $categorywinepost = Winepost::CategoryWinepostList($categoryId);
            if (count($categorywinepost) > 0) {
                $categoryName = $categorywinepost[0]->WinePostCategory->category_name;
            } else {
                $categoryName = null;
            }
        }
        $data = View::make('frontend.wine.categorywineposts', array('wineposts' => $categorywinepost, 'category' => $categoryName))->render();
        return Response::json(array('html' => $data));
    }

    /* selected category wise winepost listing
     * 
     * @return $catId
     * @date   18th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com> 
     */

    public function GetWinepostsListOnSelectedCategory($catId)
    {
        $categorywinepost = Winepost::CategoryWinepostList($catId);
        #right sidebar category list display
        $categorylist = WinePostCategory::GetCategoryCount();
        #mostviewd Wineposts list 3
        $mostviewdwinepost = Winepost::MostViewdWinepostList(3);
        #recent Wineposts list 3
        $mostrecentwinepost = Winepost::MostRecentWinepostList();
        return View::make('frontend.wine.userwineposts', array('wineposts' => $categorywinepost, 'categorys' => $categorylist, 'mostrecentwineposts' => $mostrecentwinepost, 'mostviewdwineposts' => $mostviewdwinepost));
    }

    
    /* more winepost listing
     * 
     * @return $lastid
     * @date   7th October 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com> 
     */
    public function GetWinepostOnListingViaScrollHover()
     {
         $lastId=Input::get('lastId');
         $allwinepost = Winepost::GetAllWinepostListViaLastId($lastId);
         if(count($allwinepost)>0){
         $data = View::make('frontend.wine.winepostlistsmore', array('wineposts' => $allwinepost))->render();          $error="false";
         $status=1;
         }else{
             $data='null';
             $error="true";
             $status=0;
         }
         return Response::json(array('html' => $data,'error'=>$error,'status'=>$status));
     }
     
      /* more winepost listing on category wise
     * 
     * @return $lastid and catid
     * @date   7th October 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com> 
     */
    public function GetWinepostOnListingViaScrollHoverByCategory()
     {
         $catId=Input::get('CatId');
         $WinePostId=Input::get('wineId');
         $allwinepost = Winepost::CategoryWinepostListOnScroll($catId,$WinePostId);
         if(count($allwinepost)>0){
         $data = View::make('frontend.wine.winepostlistsmoreoncategory', array('wineposts' => $allwinepost))->render();         
         $error="false";
         $status=1;
         }else{
             $data='null';
             $error="true";
             $status=0;
         }
         return Response::json(array('html' => $data,'error'=>$error,'status'=>$status));
     }
    
     
}
