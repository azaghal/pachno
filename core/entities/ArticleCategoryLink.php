<?php

    namespace pachno\core\entities;

    use pachno\core\entities\Article;
    use \pachno\core\framework;
    use pachno\core\entities\ArticleCategory;

    /**
     * @Table(name="\pachno\core\entities\tables\ArticleCategories")
     */
    class ArticleCategoryLink extends \pachno\core\entities\common\IdentifiableScoped
    {

        /**
         * The original article
         *
         * @var \pachno\core\entities\Article
         * @Column(type="integer", length=10)
         * @Relates(class="\pachno\core\entities\Article")
         */
        protected $_article_id = null;

        /**
         * The category
         *
         * @var \pachno\core\entities\ArticleCategory
         * @Column(type="integer", length=10)
         * @Relates(class="\pachno\core\entities\Category")
         */
        protected $_category_id = null;

        /**
         * @return Article
         */
        public function getArticle()
        {
            return $this->_b2dbLazyLoad('_article_id');
        }

        /**
         * @param Article|int $article_id
         */
        public function setArticle($article_id)
        {
            $this->_article_id = $article_id;
        }

        /**
         * @return ArticleCategoryLink
         */
        public function getCategory()
        {
            return $this->_b2dbLazyLoad('_category_id');
        }

        /**
         * @param ArticleCategoryLink|int $category_id
         */
        public function setCategory($category_id)
        {
            $this->_category_id = $category_id;
        }

    }
