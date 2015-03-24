<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class TastingNotes extends Eloquent implements UserInterface, RemindableInterface {

    use UserTrait,
        RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'united_tasting_notes';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    /**
     * all tasting notes Record list for admin.
     *
     * @return tasting notes list
     * @date    20th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function AllTastingNotesList() {
        return TastingNotes::with('Product', 'User')->where('is_delete', '=', 0)->get();
    }

    /**
     * all tasting notes Record list for frontend.
     *
     * @return tasting notes list
     * @date    20th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function AllTastingNotesListForntEnd() {
        return TastingNotes::with('Product', 'User')->where('is_delete', '=', 0)->where('is_status', '=', 1)->get();
    }

    /**
     * all products foregind id to get product name list resource for tastingnote related
     *
     * @return tasting notes list
     * @date   13th August 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function doGetTastingNoteAllList($userId = null, $productId = null, $tag = null, $scale = null, $searchvalue = null, $countryIds = null, $stateIds = null) {
        $query = TastingNotes::where('united_tasting_notes.is_status', '=', true)
                ->where('united_tasting_notes.is_delete', '=', false)
                ->join('united_users', 'united_tasting_notes.user_id', '=', 'united_users.id')
                ->join('united_products', 'united_tasting_notes.product_id', '=', 'united_products.id')
                ->leftjoin('united_countries', 'united_products.country_id', '=', 'united_countries.id')
                ->leftjoin('united_state', 'united_products.state_id', '=', 'united_state.id')
                ->leftjoin('united_sub_regions', 'united_products.sub_region_id', '=', 'united_sub_regions.id'
                )
                ->leftjoin('united_varietals', 'united_products.varietal_id', '=', 'united_varietals.id');

        if (isset($userId)) {
            $query = $query->where('united_users.id', $userId);
        }
        if (isset($productId)) {
            $query = $query->whereIn('united_products.id', $productId);
        }
        if (isset($tag)) {
            $query = $query->where('united_tasting_notes.tasting_tags', 'regexp', '[[:<:]]' . $tag . '[[:>:]]');
        }
        if (isset($scale)) {
             if ($scale == 0) {
                $query = $query->whereBetween('united_tasting_notes.score', array(50, 60));
            } else if ($scale == 1) {
                $query = $query->whereBetween('united_tasting_notes.score', array(60, 70));
            } else if ($scale == 2) {
                $query = $query->whereBetween('united_tasting_notes.score', array(70, 80));
            } else if ($scale == 3) {
                $query = $query->whereBetween('united_tasting_notes.score', array(80,90));
            }  else if ($scale == 4) {
                $query = $query->whereBetween('united_tasting_notes.score', array(90,100));
            }
            else if ($scale ==5) {
                $query = $query->where('united_tasting_notes.score','>=',0);
            }
         
        }
        if (isset($countryIds)) {
            $query = $query->whereIn('united_products.country_id', $countryIds);
        }
        if (isset($stateIds)) {
            $query = $query->whereIn('united_products.state_id', $stateIds);
        }

        if (isset($searchvalue)) {
            $query = $query->where(function ($sql) use ($searchvalue) {
                        $sql->where('united_products.year', '=', $searchvalue)
                                ->orWhere('united_products.title', 'LIKE', '%' . $searchvalue . '%');
                    });
        }
        $query = $query->orderBy('united_tasting_notes.id')
                ->groupBy('united_tasting_notes.id')
                ->get(array(
            DB::Raw('united_products.id,united_products.title,united_products.product_image,united_products.producer,united_products.year,united_products.wine_color,united_products.product_image,united_countries.name as countryname,united_state.name as statename,united_sub_regions.sub_region_name,united_tasting_notes.tasting_note,united_tasting_notes.tasting_note_added_date,united_tasting_notes.drink_dates,united_tasting_notes.scale,united_tasting_notes.score,united_users.username,united_users.user_image,united_tasting_notes.id as TastingnoteId,united_varietals.varietal_name,united_tasting_notes.created_at,united_products.wine_type'),
            DB::Raw('count(united_tasting_notes.id) as totalTastingNotes1,sum(united_tasting_notes.score) as totalTastingNoteScore1,avg(united_tasting_notes.score) AS average1,MIN(united_tasting_notes.drink_soonest) AS minimumTastingNoteDate1,MAX(united_tasting_notes.drink_latest) AS maximumTastingNoteDate1'),
        ));
        //$a=DB::GetQueryLog(); echo "<pre>";print_r($a);exit;
        return $query;
    }

    /**
     * all products foregind id to get product name list resource.
     *
     * @return tasting notes list
     * @date    20th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public function product() {
        return $this->belongsTo('Product')->select(array('id', 'product_image', 'title', 'producer', 'wine_color'))->where('is_delete', '=', 0)->where('is_status', '=', 1);
    }

    /**
     * users foregin key to get user name list resource.
     *
     * @return tasting notes list
     * @date    20th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public function user() {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    /**
     * selected Tasting notes Record status update resource.
     *
     * @return Tasting list
     * @date    20th june 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function UpdateStatus($status, $tastingNoteId) {
        TastingNotes::where('id', '=', $tastingNoteId)->update(array('is_status' => $status));
    }

    /**
     * selected user Tasting notes Records list for frontend.
     *
     * @return all list
     * @date   1st july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function mytastingNotes($id) {
        if (isset($id) && $id != ''):
            return TastingNotes::with('Product', 'User')->where('is_delete', '=', 0)->where('user_id', '=', $id)->get();
        else:
            return false;
        endif;
    }

    /**
     * all tags related data gets in tasting notes
     *
     * @return tasting notes list $tag
     * @date    3th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getTagFilterResults($tag, $userId = null) {
        return TastingNotes::doGetTastingNoteAllList($userId, null, $tag, null);
    }

    /**
     * all scale related data gets in tasting notes
     *
     * @return tasting notes list frnt $scale array
     * @date    3th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getScaleScoreFilterResults($scale, $userId = null) {
        return TastingNotes::doGetTastingNoteAllList($userId = null, null, null, $scale);
    }

    /**
     * all tasted date related data gets in tasting notes
     *
     * @return tasting notes list frnt $start and end date
     * @date    3th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getTastedDateFilterResults($startDate = null, $endDate = null, $userId = null) {
        $query = TastingNotes::with('Product', 'User')->where('is_delete', '=', 0);
        if ($startDate != '') {
            $query->where('tasting_note_added_date', '>=', $startDate);
        }
        if ($endDate != '') {
            $query->where('tasting_note_added_date', '<=', $endDate);
        }
        if (isset($userId)) {
            $query->where('user_id', '=', $userId);
        }
        return $query->get();
    }

    /**
     * all color related data gets in tasting notes
     *
     * @return tasting notes list $tag
     * @date   4th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getColorFilterResults($color, $userId = null) {
        #get product id via color from products
        $resultOfProducts = Product::colorRealtedProduct($color);
        $arrayOfProductIds = @explode(",", $resultOfProducts[0]->productId);
        return TastingNotes::doGetTastingNoteAllList($userId, $arrayOfProductIds);
    }

    /**
     * all country Record list resource.
     *
     * @return $country ids
     * @date    4th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getCountryFilterResults($countryId, $userId = null) {
        #get product id via countryid from products
        $results = Product::CountryProductList($countryId);
        $arrayOfProductIds = @explode(",", $results[0]->productId);
        // echo "tid=".$userId;
        //echo "<pre>";print_r($arrayOfProductIds);exit;
        return TastingNotes::doGetTastingNoteAllList($userId, $arrayOfProductIds, null, null, null, $countryId);
    }

    /**
     * all state Record list resource.
     *
     * @return $state ids
     * @date    4th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getStateFilterResults($stateId, $userId = null) {
        #get product id via stateid from products
        $results = Product::StateProductList($stateId);
        $arrayOfProductIds = @explode(",", $results[0]->productId);
        return TastingNotes::doGetTastingNoteAllList($userId, $arrayOfProductIds, null, null, null, null, $stateId);
    }

    /**
     * all tasted low score and high score scale search
     *
     * @return tasting notes list frnt low and high score
     * @date    4th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getScoreSourceFilterResults($lowscore = null, $highscore = null) {
        $query = TastingNotes::with('Product', 'User')->where('is_delete', '=', 0);
        if ($startDate != '') {
            $query->where('tasting_note_added_date', '>=', $startDate);
        }
        if ($endDate != '') {
            $query->where('tasting_note_added_date', '<=', $endDate);
        }
        return $query->get();
    }

    /**
     * tasting notes get productid for searching list display
     *
     * @return tasting notes  product ids
     * @date    7th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getProductIdForFilterResults() {
        $query = TastingNotes::where('is_delete', '=', 0)->where('is_status', '=', 1)
                ->get(array(
            DB::Raw('group_concat(product_id) as productId')
        ));
        return $query;
    }

    /**
     * tasting notes get productid on selected user wise for searching list display
     *
     * @return tasting notes  product ids
     * @date    7th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function getProductIdForFilterResultsSelected($userId) {
        $query = TastingNotes::where('is_delete', '=', 0)->where('is_status', '=', 1)
                ->where('user_id', '=', $userId)
                ->get(array(
            DB::Raw('group_concat(product_id) as productId')
        ));
        return $query;
    }

    /**
     * selected user tasting note records get
     *
     * @return $user_id
     * @date   12st july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function SelectedUsertastingNotes($id = null, $limit = null) {
        if (isset($id) && $id != ''):
            $query = TastingNotes::with('Product', 'User')->where('is_delete', '=', 0)->where('is_public', '=', 1);
            if ($id != 0) {
                $query->where('user_id', '=', $id);
            }
            if ($limit != '') {
                $query->take($limit);
            }
            $query->orderBy('created_at', 'desc')->get();
        else:
            return false;
        endif;

        if (count($query) > 0) {
            return $query;
        } else {
            return false;
        }
    }

    #get Tasting notes latest 5 for community list 
    /**
     * asting notes list for 5 latest display in community page
     *
     * @return tastingnoteslist
     * @date   22th August 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */

    public static function GetTastingNotesListForCommunityPage($limit = null) {
        $query = TastingNotes::where('united_tasting_notes.is_status', '=', true)
                ->join('united_products', 'united_tasting_notes.product_id', '=', 'united_products.id')
                ->leftjoin('united_users', 'united_tasting_notes.user_id', '=', 'united_users.id')
                ->where('united_tasting_notes.is_delete', '=', false)
                ->where('united_tasting_notes.is_public', '=', true)
                ->where('united_products.is_status', '=', '1')
                ->where('united_products.product_type', '=', 'Wine');
        if (isset($limit)) {
            $query = $query->take($limit);
        }
        $query = $query->get(array(DB::Raw('united_tasting_notes.tasting_note,united_tasting_notes.tasting_note_added_date,united_tasting_notes.id,united_tasting_notes.score,united_tasting_notes.drink_dates,united_products.id as ProductId,united_products.title,united_products.producer,united_products.year,united_users.username,united_users.user_image')));
        return $query;
    }

    /**
     * selected user tasting note records get
     *
     * @return $user_id
     * @date   19th August 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function SelectedUsertastingNoteslistForProfilePage($id) {
        $query = TastingNotes::where('united_tasting_notes.is_status', '=', true)
                ->join('united_products', 'united_tasting_notes.product_id', '=', 'united_products.id')
                ->leftjoin('united_users', 'united_tasting_notes.user_id', '=', 'united_users.id')
                ->where('united_tasting_notes.is_delete', '=', false)
                ->where('united_tasting_notes.is_public', '=', true)
                ->where('united_products.is_status', '=', '1')
                ->where('united_products.product_type', '=', 'Wine')
                ->where('united_tasting_notes.user_id', '=', $id)
                ->take(5)
                ->get(array(DB::Raw('united_tasting_notes.tasting_note,united_tasting_notes.tasting_note_added_date,united_tasting_notes.id,united_tasting_notes.score,united_tasting_notes.drink_dates,united_products.id as ProductId,united_products.title,united_products.producer,united_products.year,united_products.product_image')));
        return $query;
    }

    /**
     * all tasting notes count
     *
     * @return count
     * @date   30th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function GetTastingNotesCount($userid = null) {
        $query = TastingNotes::where('is_delete', '=', 0)->where('is_status', '=', 1);
        if (isset($query)) {
            $query = $query->where('user_id', '=', $userid);
        }
        $query = $query->count();

        if ($query > 0) {
            return $query;
        } else {
            return false;
        }
    }

    /** selected product tasting notes list get
     *
     * @return tasting notes list {product id}
     * @date    14th August 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function GetSelectedProductTastingNoteList($productId) {
        return TastingNotes::where('product_id', '=', $productId)->where('is_delete', '=', false)->where('is_status', '=', true)->get();
    }

    #insert common tasting notes for ajax

    public static function AjaxInsertTastingNotes() {
        if (Input::has('is_flawed')) {
            $is_flawed = 1;
        } else {
            $is_flawed = 0;
        }

        if (Input::has('is_public')) {
            $is_public = 1;
        } else {
            $is_public = 0;
        }

        #assign like
        if ($is_flawed == 1) {
            $like = 0;
        } else {
            if (Input::has('radioGroup')) {
                $like = Input::get('radioGroup');
            } else {
                $like = 0;
            }
        }



//        if (strpos(Input::get('scale'), 'point') !== false) {
//            $score = Input::get('input_score');
//        } else {
//            $score = Input::get('star_score');
//        }

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
        //echo "<pre>";print_r($_REQUEST);exit;
        $tasting = new Tasting;
        $tasting->product_id = Input::get('ProductId');
        $tasting->user_id = Auth::user()->id;
        // $tasting->scale = Input::get('scale');
        // $tasting->score = $score;
        $tasting->score = Input::get('input_score');
        $tasting->user_like = $like;
        $tasting->drink_soonest = Input::get('drink_soonest');
        $tasting->drink_latest = Input::get('drink_latest');
        $tasting->tasting_note = Input::get('tasting_note');
        $tasting->tasting_note_added_date = date('Y-m-d', strtotime(Input::get('tasting_note_added_date')));
        //  $tasting->drink_dates = Input::get('drink_dates');
        $tasting->tasting_tags = Input::get('tasting_tags');

        $tasting->is_flawed = $is_flawed;
        $tasting->is_public = $is_public;
        $tasting->twitter_post = $twitter_post;
        $tasting->facebook_post = $facebook_post;

        $tasting->created_at = date('Y-m-d h:i:s');
        $tasting->updated_at = date('Y-m-d h:i:s');
        $tasting->save();

        $productTitle = Product::where('id', '=', Input::get('ProductId'))->first();
        $url = Config::get('app.url') . "tastingnotes/wine-details/" . $productTitle->id . "/" . Str::slug($productTitle->title);
        #twitterPosting
        if ($twitter_post == 1) {
            if (Session::has('tw_oauth_request_token') && Session::has('tw_oauth_request_token_secret')) {
                $tweetString = substr(Input::get('tasting_note'), 0, 85);
                $result = User::UserTweetViaWebsite($tweetString, $url);
            }
        }

        #facebook post
        if ($facebook_post == 1) {
            if (Session::has('fb_oauth_request_token')) {
                $postString = $productTitle->year . "-" . $productTitle->title . " \n" . substr(Input::get('tasting_note'), 0, 200);
                $result = User::UserFacebookPostViaWebsite($postString, $url, 'Tasting Notes');
            }
        }
        return $tasting->id;
    }

    /**
     *
     * most tasting notes userlist for frontend home page
     *
     * @return most tasting userlist
     * @date    3rd September 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function doPostMycellarTwitterPost($productId) {
        $productTitle = Product::where('id', '=', $productId)->first();
        $url = Config::get('app.url') . "tastingnotes/wine-details/" . $productTitle->id . "/" . Str::slug($productTitle->title);
        $string = $productTitle->year . " " . ucfirst($productTitle->title) . " -- " . $productTitle->producer;

        #twitterPosting       
        if (Session::has('tw_oauth_request_token') && Session::has('tw_oauth_request_token_secret')) {
            $tweetString = substr($string, 0, 85);
            $result = User::UserTweetViaWebsite($tweetString, $url);
        }
    }

    /**
     *
     * most tasting notes userlist for frontend home page
     *
     * @return most tasting userlist
     * @date    3rd September 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */
    public static function GetMostAcvtiveTastingNotesUser($limit = null) {
        $query = TastingNotes::where('united_tasting_notes.is_status', '=', true)
                ->join('united_products', 'united_tasting_notes.product_id', '=', 'united_products.id')
                ->join('united_users', 'united_tasting_notes.user_id', '=', 'united_users.id')
                ->join('united_countries', 'united_users.user_country', '=', 'united_countries.id')
                ->join('united_state', 'united_users.user_state', '=', 'united_state.id')
                ->where('united_tasting_notes.is_delete', '=', false)
                ->where('united_users.user_type', '!=', 'admin')
                ->where('united_users.is_status', '=', true)
                ->groupBy('united_users.id')
                ->orderBy('totalTastinNote', 'desc')
                ->take($limit)
                ->get(array(
            DB::Raw('united_users.id,united_users.username,united_users.user_firstname,united_users.user_lastname,united_countries.name as countryName,united_state.name as stateName,count(united_tasting_notes.user_id) as totalTastinNote,united_users.user_image,united_products.product_image')
        ));
        return $query;
    }

    #pending NEED TO CHECK WITH MANISH SIR
    /**
     * ( Need To check this below functionality)
     *
     *
     * all tasted date related data gets in tasting notes
     *
     * @return tasting notes list frnt $start and end date
     * @date    3th july 2014
     * @author Ashish Ranpara <ashishranpara@gmail.com>
     */

    public static function getDrinkDateFilterResults($startDate = null, $endDate = null) {
        $query = TastingNotes::with('Product', 'User')->where('is_delete', '=', 0);
        if ($startDate != '') {
            $query->where('drink_dates', '>=', $startDate);
        }
        if ($endDate != '') {
            $query->where('drink_dates', '<=', $endDate);
        }
        $query->get();

        $a = DB::getQueryLog();
        echo "<pre>";
        print_r($a);
        print_r($query);
        exit;
    }

}

