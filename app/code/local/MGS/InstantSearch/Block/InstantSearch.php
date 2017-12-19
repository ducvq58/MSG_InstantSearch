<?php

class MGS_InstantSearch_Block_InstantSearch extends Mage_Core_Block_Template
{
	
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	private $_itemPerPage = 4;
    private $_pageFrame = 8;
    private $_curPage = 1;
    private $limit; 
    private $viewMoreLabel;
    

    public function _construct()
    {   
        $this->limit = Mage::helper('instantsearch/data')->getLimit();;
        $this->viewMoreLabel = Mage::helper('instantsearch/data')->getViewMoreLabel();
    }

    public function getCollection($collection = 'null')
    {
        if($collection != 'null'){
            $page = $this->getRequest()->getParam('page');
            if($page) $this->_curPage = $page;
            
            $collection->setCurPage($this->_curPage);
            $collection->setPageSize($this->_itemPerPage);
            return $collection;
        }
    }

	public function getPagerHtml($collection = 'null')
    {    
        $html = false;
        if($collection == 'null') return;
        if($collection->getSize() > $this->_itemPerPage)
        {
            $curPage = $this->getRequest()->getParam('page');
            $type = $this->getRequest()->getParam('type');
            $query = $this->getRequest()->getParam('q');
            $pager = (int)($collection->getSize() / $this->_itemPerPage);
            $count = ($collection->getSize() % $this->_itemPerPage == 0) ? $pager : $pager + 1 ;
            $url = $this->getPagerUrl();
            $start = 1;
            $end = $this->_pageFrame;
            
            $html .= '<ol>';
            if(isset($curPage) && $curPage != 1){
                $start = $curPage - 1;                                        
                $end = $start + $this->_pageFrame;
            }else{
                $end = $start + $this->_pageFrame;
            }
            if($end > $count){
                $start = $count - ($this->_pageFrame-1);
            }else{
                $count = $end-1;
            }
            
            for($i = $start; $i<=$count; $i++)
            {
                if($i >= 1){
                    if($curPage){
                        $html .= ($curPage == $i) ? '<li class="current">'. $i .'</li>' : '<li><a href="'.$url.'?type='.$type.'&q='.$query.'&page='.$i.'">'. $i .'</a></li>';
                    }else{
                        $html .= ($i == 1) ? '<li class="current">'. $i .'</li>' : '<li><a href="'.$url.'?type='.$type.'&q='.$query.'&page='.$i.'">'. $i .'</a></li>';
                    }
                }
                
            }
            $html .= '</ol>';
        }
        
        return $html;
    }

    public function getPagerUrl()
    {
        $cur_url = Mage::helper('core/url')->getCurrentUrl();
        $url_parts = parse_url($cur_url);
    	$constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];

    	return $constructed_url;
    }

    public function getSearchProductPages($keyword)
    {
        $products = Mage::getResourceModel("catalog/product_collection")->addAttributeToSelect("*");
        $query = Mage::helper("catalogSearch")->getQuery();
        $query->setQueryText($keyword);
        $products = $query->getSearchCollection();
        $products->addSearchFilter($keyword);
        $products->addAttributeToSelect("*");
        $products->addAttributeToFilter("status", 1);


        return $products;
    }

    public function getSearchProductHtml($type)
    {
        $storeId = Mage::app()->getStore()->getId();

        $keyword = $this->getRequest()->getParam('q');
        if ($type == 'dropdown') {
            $html = '<p class="instantsearch-title">Product</p>';
        }
        else{
            $html = '<h2>Product search results for "' . $keyword . '"</h2><hr>';
        }
        $products = $this->getSearchProductPages($keyword);
        if(!$products->count()) 
        {
            $html .= '<p>No result</p>';
        }
        else
        {
            $result_count = 1;
            foreach ($products as $product) {
                if($result_count++ > $this->limit) {
                    $html .= '<form class= "viewMoreLabel" action="' . Mage::getUrl('catalogsearch/result/') . '" method="get">
                                <input type="hidden" name="q" value="' . $keyword . '">
                                <input type="submit" value="' . $this->viewMoreLabel . '">
                            </form>';
                    break;
                }
                $summaryData = Mage::getModel('review/review_summary')->setStoreId($storeId)->load($product->getId());
                $html .= '
                <a class="instantsearch-item" href="' . $product->getProductUrl() . '?>">
                <div>
                    <img src="' . Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(80,80) .'">
                </div>
                <div class="product-info">
                    <p style="font-weight: bold;">' . $product->name . '</p>';
                if (Mage::helper('instantsearch/data')->showReview() && $summaryData['rating_summary']){
                    $html .= '<div class="ratings">
                        <div class="rating-box">
                            <div class="rating" style="width:' . $summaryData['rating_summary'] . '%"></div>
                        </div>
                    </div>';
                }
                if(Mage::helper('instantsearch/data')->showShortDescription()){
                    $html .= '<p>' . $product->short_description . '</p>';
                }
                $productBlock = $this->getLayout()->createBlock('catalog/product_price');
                $html .=  $productBlock->getPriceHtml($product);
                $html .=   '</div></a>';
            }
        }

        return $html;
    }

    public function getSearchCategoryPages($keyword)
    {
        $categories = Mage::getModel('catalog/category')->getCollection()
        ->addAttributeToSelect('url')
        ->addAttributeToSelect('name')
        ->addAttributeToFilter('name',array(array('like' => '%'. $keyword.'%')));

        return $categories;
    }

    public function getSearchCategoryHtml($type)
    {
        $keyword = $this->getRequest()->getParam('q');
        if ($type == 'dropdown') {
            $html = '<p class="instantsearch-title">Category</p>';
        }
        else{
            $html = '<h2>Category search results for "' . $keyword . '"</h2><hr>';
        }
        $categories = $this->getSearchCategoryPages($keyword);
        if(!$categories->count()) 
        {
            $html .= '<p>No result</p>';
        }
        else
        {
            $result_count = 1;
            foreach ($categories as $cat){
                if ($type != 'category') {
                    if($result_count++ > $this->limit) {
                        $html .= '<form class= "viewMoreLabel" action="' . Mage::getUrl('instantsearch/') . '" method="get">
                                    <input type="hidden" name="type" value="category">
                                    <input type="hidden" name="q" value="' . $keyword . '">
                                    <input type="submit" value="' . $this->viewMoreLabel . '">
                                </form>';
                        break;
                    }
                }
                
                $html .= '<a href="' . $cat->getUrl() . '" class="instantsearch-category">' . $cat->getName() . '</a>&nbsp&nbsp';

                if ($type == 'category') $html .= '<br>';
            }
        }

        return $html;
    }

    public function getSearchCMSPages($keyword)
    {
        $storeId = Mage::app()->getStore()->getId();
        $cmspages = Mage::getModel('cms/page')->getCollection()
        ->addFieldToFilter("is_active",1)
        ->addFieldToFilter(
            array('title','content'),
            array(
                array('like'=>'%'. $keyword.'%'),
                array('like'=>'%'. $keyword.'%')
            )
        )
        ->setOrder('title','ASC');
        
        $cmspages->load();
        return $cmspages;
    }

    public function getSearchCMSHtml($type)
    {
        $keyword = $this->getRequest()->getParam('q');
        if ($type == 'dropdown') {
            $html = '<p class="instantsearch-title">CMS Page</p>';
        }
        else{
            $html = '<h2>CMS Page search results for "' . $keyword . '"</h2><hr>';
        }
        $cms_page = $this->getSearchCmsPages($keyword);
        if(!$cms_page->count()) 
        {
            $html .= '<p>No result</p>';
        }
        else
        {
            $result_count = 1;
            foreach ($cms_page as $cms_item){
                if ($type != 'cms') {
                    if($result_count++ > $this->limit) {
                        $html .= '<form class= "viewMoreLabel" action="' . Mage::getUrl('instantsearch/') . '" method="get">
                                    <input type="hidden" name="type" value="cms">
                                    <input type="hidden" name="q" value="' . $keyword . '">
                                    <input type="submit" value="' . $this->viewMoreLabel . '">
                                </form>';
                        break;
                    }
                }
                $html .= '<a href="' . $cms_item->getIdentifier() . '" class="instantsearch-category">' . $cms_item->getTitle() . '</a>&nbsp&nbsp';
                if ($type == 'cms') $html .= '<br>';
            }
        }

        return $html;
    }

    public function getSearchBlogPages($keyword)
    {
        $posts = Mage::getModel('blog/blog')->getCollection()
        ->addFieldToFilter(
            array('title','short_content'),
            array(
                array('like'=>'%'. $keyword.'%'),
                array('like'=>'%'. $keyword.'%')
            )
        );
        return $posts;
    } 

    public function getSearchBlogHtml($type)
    {
        $keyword = $this->getRequest()->getParam('q');
        if ($type == 'dropdown') {
            $html = '<p class="instantsearch-title">Blog</p>';
        }
        else{
            $html = '<h2>Blog search results for "' . $keyword . '"</h2><hr>';
        }
        
        $posts = $this->getSearchBlogPages($keyword);

        if(!$posts->count()) 
        {
            $html .= '<p>No result</p>';
        }
        else 
        {
            if ($type == 'blog') 
            {
                if ($this->getPagerHtml($posts)) {
                    $html .= '<div class="pages">
                                <span>Page : </span>'
                                . $this->getPagerHtml($posts) . 
                            '</div>';
                }
                $posts = $this->getSearchBlogPages($keyword);
                $posts = $this->getCollection($posts);
                foreach ($posts as $post) {
                    $html .= '
                    <div class="postWrapper">
                        <div class="postTitle">
                            <h2><a href="' . Mage::getUrl('blog') . $post->getIdentifier() . '">'. $post->getTitle() . '</a></h2>
                            <h3>' . $post->getCreatedTime() . '</h3>
                        </div>
                        <div class="postContent std">'
                            . $post->getShortContent() .
                            '<a class="aw-blog-read-more" href="' . $post->getAddress() . '">Read More</a>
                        </div>
                    </div>';
                }
            }
            elseif ($type == 'dropdown')
            {
                $result_count = 1;
                foreach ($posts as $post) {
                    if($result_count++ > $this->limit) {
                        $html .= '<form class= "viewMoreLabel" action="' . Mage::getUrl('instantsearch/') . '" method="get">
                                    <input type="hidden" name="type" value="blog">
                                    <input type="hidden" name="q" value="' . $keyword . '">
                                    <input type="submit" value="' . $this->viewMoreLabel . '">
                                </form>';
                        break;
                    }
                    $html .= '<a href="' . Mage::getUrl('blog') . $post->getIdentifier() . '" >' . $post->getTitle() . '</a>
                    <br>';
                }
            }
            else 
            {
                $result_count = 1;
                foreach ($posts as $post) {
                    if($result_count++ > $this->limit) {
                        $html .= '<form class= "viewMoreLabel" action="' . Mage::getUrl('instantsearch/') . '" method="get">
                                    <input type="hidden" name="type" value="blog">
                                    <input type="hidden" name="q" value="' . $keyword . '">
                                    <input type="submit" value="' . $this->viewMoreLabel . '">
                                </form>';
                        break;
                    }
                    $html .= '
                    <div class="postWrapper">
                        <div class="postTitle">
                            <h2><a href="' . Mage::getUrl('blog') . $post->getIdentifier() . '">'. $post->getTitle() . '</a></h2>
                            <h3>' . $post->getCreatedTime() . '</h3>
                        </div>
                        <div class="postContent std">'
                            . $post->getShortContent() .
                            '<a class="aw-blog-read-more" href="' . $post->getAddress() . '">Read More</a>
                        </div>
                    </div>';
                }
            } 
            
        }
        
        return $html;
    }

    public function getPosition(){
        $productPosition = Mage::helper('instantsearch/data')->getProductDropdownPosition();
        $categoryPosition = Mage::helper('instantsearch/data')->getCategoryDropdownPosition();
        $cmsPosition = Mage::helper('instantsearch/data')->getCmsDropdownPosition();
        $blogPosition = Mage::helper('instantsearch/data')->getBlogDropdownPosition();

        $data_sort = array(
            array("title"=>"product", "position" => $productPosition),
            array("title"=>"category", "position" => $categoryPosition),
            array("title"=>"cms", "position" => $cmsPosition),
            array("title"=>"blog", "position" => $blogPosition),
        );

        foreach ($data_sort as $key => $row) {
            $title[$key]  = $row['title'];
            $position[$key] = $row['position'];
        }
        array_multisort($position, SORT_ASC, $title, SORT_ASC, $data_sort);

        return $data_sort;
    }

}