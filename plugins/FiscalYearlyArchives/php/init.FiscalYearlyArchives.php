<?php
# Movable Type (r) (C) 2001-2020 Six Apart Ltd. All Rights Reserved.
# This code cannot be redistributed without permission from www.sixapart.com.
# For more information, consult your Movable Type license.
#
# $Id$

require_once('archive_lib.php');
require_once('MTUtil.php');

ArchiverFactory::add_archiver('Fiscal-Yearly', 'FiscalYearlyArchiver');
ArchiverFactory::add_archiver('ContentType-Fiscal-Yearly', 'ContentTypeFiscalYearlyArchiver');
ArchiverFactory::add_archiver('Author-Fiscal-Yearly', 'AuthorFiscalYearlyArchiver');
ArchiverFactory::add_archiver('ContentType-Author-Fiscal-Yearly', 'ContentTypeAuthorFiscalYearlyArchiver');
ArchiverFactory::add_archiver('Category-Fiscal-Yearly', 'CategoryFiscalYearlyArchiver');
ArchiverFactory::add_archiver('ContentType-Category-Fiscal-Yearly', 'ContentTypeCategoryFiscalYearlyArchiver');

class FiscalYearlyArchiver extends DateBasedArchiver {

    // Override Method
    public function get_label($args = null) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        require_once("l10n_$lang.php");
        return $mt->translate('FISCAL-YEARLY_ADV');
    }

    public function get_title($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $stamp = $ctx->stash('current_timestamp');
        list($start) = start_end_fiscal_year($stamp, $ctx->stash('blog'));
        $format = $args['format'];
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        if (strtolower($lang) == 'jp' || strtolower($lang) == 'ja') {
            $format or $format = "%Y&#24180;&#24230;";
        } else {
            $format or $format = "FY%Y";
        }

        return $ctx->_hdlr_date(array('ts' => $start, 'format' => $format), $ctx);
    }

    public function get_range($period_start) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        if (is_array($period_start))
            $period_start = sprintf("%04d", $period_start['y']);

        return start_end_fiscal_year($period_start, $ctx->stash('blog'));
    }

    protected function get_update_link_args($results) {
        $args = array();
        if (!empty($results)) {
            $count = count($results);
            $args['hi'] = sprintf("%04d1231235959", $results[0]['y']);
            $args['low'] = sprintf("%04d0101000000", $results[$count - 1]['y']);
        }
        return $args;
    }

    public function get_template_params() {
        $array = parent::get_template_params();
        $array['datebased_only_archive']          = 1;
        $array['datebased_fiscal_yearly_archive'] = 1;
        $array['archive_class']                   = 'fiscal-yearly-archive';
        return $array;
    }

    public function template_params() {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $vars =& $ctx->__stash['vars'];
        $vars += FiscalYearlyArchiver::get_template_params();
    }

    protected function get_archive_list_data($args) {
        $mt = MT::get_instance();

        $blog_id = $args['blog_id'];
        $at = $args['archive_type'];
        $order = $args['sort_order'] == 'ascend' ? 'asc' : 'desc';

        $year_ext = $mt->db()->apply_extract_date('year', 'entry_authored_on');
        $month_ext = $mt->db()->apply_extract_date('month', 'entry_authored_on');
        
        $sql = "
                select count(*) as entry_count,
                       $year_ext as y,
                       $month_ext as m
                  from mt_entry
                 where entry_blog_id = $blog_id
                   and entry_status = 2
                   and entry_class = 'entry'
                   $date_filter
                 group by
                       $year_ext,
                       $month_ext
                 order by
                       $year_ext $order,
                       $month_ext $order";

        $limit = isset($args['lastn']) ? $args['lastn'] : -1;
        $offset = isset($args['offset']) ? $args['offset'] : -1;
        $results = $mt->db()->SelectLimit($sql, $limit, $offset);

        if (empty($results))
            return; 

        $temp_hash;
        foreach ($results->GetArray() as $row) {
            $date = sprintf("%04d%02d01000000", $row[1], $row[2]);
            list($start) = start_end_fiscal_year($date);
            $y = intval(substr($start, 0, 4));
            $temp_hash[$y]++;
        }
        $rows;
        foreach ($temp_hash as $key=>$val) {
            $rows[] = [ 'entry_count' => $val, 'y' => $key ];
        }

        return $rows;
    }

    protected function get_helper() {
        return 'start_end_fiscal_year';
    }
}

class ContentTypeFiscalYearlyArchiver extends ContentTypeDateBasedArchiver {
    
    public function get_label($args = null) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        require_once("l10n_$lang.php");
        return $mt->translate('CONTENTTYPE-FISCAL-YEARLY_ADV');
    }
    
    public function get_title($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $stamp = $ctx->stash('current_timestamp');
        list($start) = start_end_fiscal_year($stamp, $ctx->stash('blog'));
        $format = $args['format'];
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        if (strtolower($lang) == 'jp' || strtolower($lang) == 'ja') {
            $format or $format = "%Y&#24180;&#24230;";
        } else {
            $format or $format = "FY%Y";
        }

        return $ctx->_hdlr_date(array('ts' => $start, 'format' => $format), $ctx);
    }

    public function get_range($period_start) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        if (is_array($period_start))
            $period_start = sprintf("%04d", $period_start['y']);

        return start_end_fiscal_year($period_start, $ctx->stash('blog'));
    }

    protected function get_update_link_args($results) {
        $args = array();
        if (!empty($results)) {
            $count = count($results);
            $args['hi'] = sprintf("%04d1231235959", $results[0]['y']);
            $args['low'] = sprintf("%04d0101000000", $results[$count - 1]['y']);
        }
        return $args;
    }

    public function get_template_params() {
        $array = parent::get_template_params();
        $array['datebased_only_archive']          = 1;
        $array['datebased_fiscal_yearly_archive'] = 1;
        $array['archive_class']                   = 'contenttype-fiscal-yearly-archive';
        return $array;
    }

    public function template_params() {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $vars =& $ctx->__stash['vars'];
        $vars += ContentTypeFiscalYearlyArchiver::get_template_params();
    }

    protected function get_archive_list_data($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $blog_id = $args['blog_id'];
        $at = $args['archive_type'];
        $order = $args['sort_order'] == 'ascend' ? 'asc' : 'desc';

        $content_type_filter = _get_content_type_filter($args);

        list($dt_target_col, $cat_target_col, $join_on) = _get_join_on($ctx, $at, $blog_id);

        $year_ext = $mt->db()->apply_extract_date('year', $dt_target_col);
        $month_ext = $mt->db()->apply_extract_date('month', $dt_target_col);

        $sql = "
                select count(*) as cd_count,
                       $year_ext as y,
                       $month_ext as m
                  from mt_cd
                  $join_on
                 where cd_blog_id = $blog_id
                   and cd_status = 2
                   $date_filter
                   $content_type_filter
                 group by
                       $year_ext,
                       $month_ext
                 order by
                       $year_ext $order,
                       $month_ext $order";

        $limit = isset($args['lastn']) ? $args['lastn'] : -1;
        $offset = isset($args['offset']) ? $args['offset'] : -1;
        $results = $mt->db()->SelectLimit($sql, $limit, $offset);

        if (empty($results))
            return; 

        $temp_hash;
        foreach ($results->GetArray() as $row) {
            $date = sprintf("%04d%02d01000000", $row[1], $row[2]);
            list($start) = start_end_fiscal_year($date);
            $y = intval(substr($start, 0, 4));
            $temp_hash[$y]++;
        }
        $rows;
        foreach ($temp_hash as $key=>$val) {
            $rows[] = [ 'entry_count' => $val, 'y' => $key ];
        }

        return $rows;
    }

    protected function get_helper() {
        return 'start_end_fiscal_year';
    }
}

class AuthorFiscalYearlyArchiver extends DateBasedAuthorArchiver {

    public function get_label($args = null) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        require_once("l10n_$lang.php");
        return $mt->translate('AUTHOR-FISCAL-YEARLY_ADV');
    }

    public function get_title($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $author_name = parent::get_author_name();
        $stamp = $ctx->stash('current_timestamp');
        list($start) = start_end_fiscal_year($stamp, $ctx->stash('blog'));
        $format = isset($args['format']) ? $args['format'] : null;
        $blog = $ctx->stash('blog');

        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
            if (strtolower($lang) == 'jp' || strtolower($lang) == 'ja') {
            $format or $format = "%Y&#24180;&#24230;";
        } else {
            $format or $format = "FY%Y";
        }

        return encode_html( strip_tags( $author_name ) )
            . $ctx->_hdlr_date(array('ts' => $start, 'format' => $format), $ctx);
    }

    public function get_range($period_start) {
        if (is_array($period_start))
            $period_start = sprintf("%04d%02d", $period_start['y'], isset($period_start['m']) ? $period_start['m'] : 1);
        return start_end_fiscal_year($period_start);
    }

    protected function get_archive_list_data($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $blog_id = $args['blog_id'];
        $order = !empty($args['sort_order']) && $args['sort_order'] == 'ascend' ? 'asc' : 'desc';
        $auth_order = !empty($args['sort_order']) && $args['sort_order'] == 'descend' ? 'desc' : 'asc';
        $year_ext = $mt->db()->apply_extract_date('year', 'entry_authored_on');
        $index = $ctx->stash('index_archive');
        #if (!$index) {
            $author = $ctx->stash('archive_author');
            $author or $author = $ctx->stash('author');
            if (isset($author)) {
                $author_filter = " and entry_author_id=".$author->author_id;
            }
        #}
        $sql = implode(' ', array(
            "select count(*) as record_count, $year_ext as y, entry_author_id, author_name",
            "from mt_entry join mt_author on entry_author_id = author_id",
            "where entry_blog_id = $blog_id and entry_class = 'entry' and entry_status = 2",
            isset($author_filter) ? $author_filter : '',
            "group by $year_ext, entry_author_id, author_name order by author_name $auth_order, $year_ext $order"
        ));

        $limit = isset($args['lastn']) ? $args['lastn'] : -1;
        $offset = isset($args['offset']) ? $args['offset'] : -1;
        $results = $mt->db()->SelectLimit($sql, $limit, $offset);
        return empty($results) ? null : $results->GetArray();
    }

    public function get_template_params() {
        $array = parent::get_template_params();
        $array['author_fiscal_yearly_archive']    = 1;
        $array['datebased_fiscal_yearly_archive'] = 1;
        $array['archive_class']                   = 'author-fiscal-yearly-archive';
        return $array;
    }

    public function template_params() {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $vars =& $ctx->__stash['vars'];
        $vars += AuthorFiscalYearlyArchiver::get_template_params();
    }

    protected function get_helper() {
        return 'start_end_fiscal_year';
    }

    protected function get_update_link_args($results) {
        $args = array();
        if (!empty($result)) {
            $count = count($results);
            
            $args['hi'] = sprintf("%04d0000000000", $results[0]['y']);
            $args['low'] = sprintf("%04d0000000000", $results[$count - 1]['y']);
        }
        return $args;
    }
}

class CategoryFiscalYearlyArchiver extends DateBasedCategoryArchiver {

    public function get_label($args = null) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        require_once("l10n_$lang.php");
        return $mt->translate('CATEGORY-FISCAL-YEARLY_ADV');
    }

    public function get_title($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $cat_name = parent::get_category_name();
        $stamp = $ctx->stash('current_timestamp');
        list($start) = start_end_fiscal_year($stamp, $ctx->stash('blog'));
        $format = isset($args['format']) ? $args['format'] : null;
        $blog = $ctx->stash('blog');

        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
            if (strtolower($lang) == 'jp' || strtolower($lang) == 'ja') {
            $format or $format = "%Y&#24180;&#24230;";
        } else {
            $format or $format = "FY%Y";
        }
        return encode_html( strip_tags( $cat_name ) )
            . $ctx->_hdlr_date(array('ts' => $start, 'format' => $format), $ctx);
    }

    public function get_range($period_start) {
        if (is_array($period_start))
            $period_start = sprintf("%04d", $period_start['y']);
        return start_end_fiscal_year($period_start);
    }

    protected function get_archive_list_data($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $blog_id = $args['blog_id'];
        $order = !empty($args['sort_order']) && $args['sort_order'] == 'ascend' ? 'asc' : 'desc';
        $cat_order = !empty($args['sort_order']) && $args['sort_order'] == 'descend' ? 'desc' : 'asc';
        $year_ext = $mt->db()->apply_extract_date('year', 'entry_authored_on');

        $index = $ctx->stash('index_archive');
        $inside = $ctx->stash('inside_archive_list');
        if (!isset($inside)) {
          $inside = false;
        }
        if ($inside) {
            $ts = $ctx->stash('current_timestamp');
            $tsend = $ctx->stash('current_timestamp_end');
            if ($ts && $tsend) {
                $ts = $mt->db()->ts2db($ts);
                $tsend = $mt->db()->ts2db($tsend);
                $date_filter = "and entry_authored_on between '$ts' and '$tsend'";
            }
        }
        #if (!$index) {
            $cat = $ctx->stash('archive_category');
            $cat or $cat = $ctx->stash('category');
            if (isset($cat)){
                $cat_filter = " and placement_category_id=".$cat->category_id;

            }
        #}
        $sql = join(' ', array(
            "select count(*) as entry_count,
                $year_ext as y,
                placement_category_id,
                category_label
            from mt_entry join mt_placement on entry_id = placement_entry_id
            join mt_category on placement_category_id = category_id
            where entry_blog_id = $blog_id
                and entry_status = 2
                and entry_class = 'entry'",
            isset($cat_filter) ? $cat_filter : '',
            isset($date_filter) ? $date_filter : '',
            "group by $year_ext, placement_category_id, category_label",
            "order by category_label $cat_order, $year_ext $order"
        ));
        $limit = isset($args['lastn']) ? $args['lastn'] : -1;
        $offset = isset($args['offset']) ? $args['offset'] : -1;
        $results = $mt->db()->SelectLimit($sql, $limit, $offset);
        return empty($results) ? null : $results->GetArray();
    }

    public function get_template_params() {
        $array = parent::get_template_params();
        $array['category_fiscal_yearly_archive']  = 1;
        $array['datebased_fiscal_yearly_archive'] = 1;
        $array['archive_class']                   = 'category-fiscal-yearly-archive';
        return $array;
    }

    public function template_params() {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $vars =& $ctx->__stash['vars'];
        $vars += CategoryFiscalYearlyArchiver::get_template_params();
    }

    protected function get_helper() {
        return 'start_end_fiscal_year';
    }

    protected function get_update_link_args($results) {
        $args = array();
        if (!empty($result)) {
            $count = count($results);
            
            $args['hi'] = sprintf("%04d0000000000", $results[0]['y']);
            $args['low'] = sprintf("%04d0000000000", $results[$count - 1]['y']);
        }
        return $args;
    }
}

class ContentTypeAuthorFiscalYearlyArchiver extends ContentTypeDateBasedAuthorArchiver {

    public function get_label($args = null) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        require_once("l10n_$lang.php");
        return $mt->translate('CONTENTTYPE-AUTHOR-FISCAL-YEARLY_ADV');
    }

    public function get_title($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $author_name = parent::get_author_name();
        $stamp = $ctx->stash('current_timestamp');
        list($start) = start_end_fiscal_year($stamp, $ctx->stash('blog'));
        $format = !empty($args['format']) ? $args['format'] : null;
        $blog = $ctx->stash('blog');

        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
            if (strtolower($lang) == 'jp' || strtolower($lang) == 'ja') {
            $format or $format = "%Y&#24180;&#24230;";
        } else {
            $format or $format = "FY%Y";
        }

        return encode_html( strip_tags( $author_name ) )
            . $ctx->_hdlr_date(array('ts' => $start, 'format' => $format), $ctx);
    }

    public function get_range($period_start) {
        if (is_array($period_start))
            $period_start = sprintf("%04d%02d", $period_start['y'], isset($period_start['m']) ? $period_start['m'] : 1);
        return start_end_fiscal_year($period_start);
    }

    protected function get_archive_list_data($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $blog_id = $args['blog_id'];
        $at = $args['archive_type'];
        $order = !empty($args['sort_order']) && $args['sort_order'] == 'ascend' ? 'asc' : 'desc';
        $auth_order = !empty($args['sort_order']) && $args['sort_order'] == 'descend' ? 'desc' : 'asc';

        $content_type_filter = _get_content_type_filter($args);

        list($dt_target_col, $cat_target_col, $join_on) = _get_join_on($ctx, $at, $blog_id);

        $year_ext = $mt->db()->apply_extract_date('year', $dt_target_col);

        $author = $ctx->stash('archive_author');
        $author or $author = $ctx->stash('author');
        if (isset($author)) {
            $author_filter = " and cd_author_id=".$author->author_id;
        }

        $sql = implode(' ', array(
            "select count(*) as record_count, $year_ext as y, cd_author_id, author_name",
            "from mt_cd",
            "join mt_author on cd_author_id = author_id $join_on",
            "where cd_blog_id = $blog_id and cd_status = 2",
            isset($author_filter) ? $author_filter : '',
            isset($content_type_filter) ? $content_type_filter : '',
            "group by $year_ext, cd_author_id, author_name",
            "order by author_name $auth_order, $year_ext $order",
        ));

        $limit = isset($args['lastn']) ? $args['lastn'] : -1;
        $offset = isset($args['offset']) ? $args['offset'] : -1;
        $results = $mt->db()->SelectLimit($sql, $limit, $offset);
        return empty($results) ? null : $results->GetArray();
    }

    public function get_template_params() {
        $array = parent::get_template_params();
        $array['author_fiscal_yearly_archive']    = 1;
        $array['datebased_fiscal_yearly_archive'] = 1;
        $array['archive_class']                   = 'contenttype-author-fiscal-yearly-archive';
        return $array;
    }

    public function template_params() {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $vars =& $ctx->__stash['vars'];
        $vars += ContentTypeAuthorFiscalYearlyArchiver::get_template_params();
    }

    protected function get_helper() {
        return 'start_end_fiscal_year';
    }

    protected function get_update_link_args($results) {
        $args = array();
        if (!empty($result)) {
            $count = count($results);
            
            $args['hi'] = sprintf("%04d0000000000", $results[0]['y']);
            $args['low'] = sprintf("%04d0000000000", $results[$count - 1]['y']);
        }
        return $args;
    }
}

class ContentTypeCategoryFiscalYearlyArchiver extends ContentTypeDateBasedCategoryArchiver {

    public function get_label($args = null) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $blog = $ctx->stash('blog');
        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
        require_once("l10n_$lang.php");
        return $mt->translate('CONTENTTYPE-CATEGORY-FISCAL-YEARLY_ADV');
    }

    public function get_title($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $cat_name = parent::get_category_name();
        $stamp = $ctx->stash('current_timestamp');
        list($start) = start_end_fiscal_year($stamp, $ctx->stash('blog'));
        $format = isset($args['format']) ? $args['format'] : null;
        $blog = $ctx->stash('blog');

        $lang = ($blog && $blog->blog_language ? $blog->blog_language :
            $mt->config('DefaultLanguage'));
            if (strtolower($lang) == 'jp' || strtolower($lang) == 'ja') {
            $format or $format = "%Y&#24180;&#24230;";
        } else {
            $format or $format = "FY%Y";
        }
        return encode_html( strip_tags( $cat_name ) )
            . $ctx->_hdlr_date(array('ts' => $start, 'format' => $format), $ctx);
    }

    public function get_range($period_start) {
        if (is_array($period_start))
            $period_start = sprintf("%04d", $period_start['y']);
        return start_end_fiscal_year($period_start);
    }

    protected function get_archive_list_data($args) {
        $mt = MT::get_instance();
        $ctx =& $mt->context();

        $content_type_filter = _get_content_type_filter($args);

        $blog_id = $args['blog_id'];
        $at = $args['archive_type'];
        $order = !empty($args['sort_order']) && $args['sort_order'] == 'ascend' ? 'asc' : 'desc';
        $cat_order = !empty($args['sort_order']) && $args['sort_order'] == 'descend' ? 'desc' : 'asc';
        $cat = $ctx->stash('archive_category');
        $cat or $cat = $ctx->stash('category');
        if ($cat) {
            $cats = array($cat);
        }
        else {
            $cat_set_id = isset($args['category_set_id']) ? $args['category_set_id'] : null;
            if (!isset($cat_set_id)) {
                $category_set = $ctx->stash('category_set');
                $cat_set_id = isset($category_set) ? $category_set->category_set_id: '> 0';
            }
            $sort_order = isset($args['sort_order']) ? $args['sort_order'] : null;
            $sort_order or $sort_order = 'ascend';
            $cats = $ctx->mt->db()->fetch_categories(array(
                'blog_id' => $blog_id,
                'show_empty' => 1,
                'class' => 'category',
                'category_set_id' => $cat_set_id,
                'sort_by' => 'label',
                'sort_order' => $sort_order
            ));
        }

        $categories = array();
        $seen_join_on = array();
        foreach ( $cats as $cat ) {
            $objectcategories = $mt->db()->fetch_objectcategory(array('category_id' => array($cat->category_id)));
            $objectcategories = $objectcategories ? $objectcategories : array();
            $cat_field_ids = array();
            foreach ( $objectcategories as $objectcategory ) {
                $cat_field_ids[$objectcategory->objectcategory_cf_id] = 1;
            }
            foreach ( $cat_field_ids as $cat_field_id => $count ) {
                list($dt_target_col, $cat_target_col, $join_on) = _get_join_on($ctx, $at, $blog_id, $cat, $cat_field_id);

                # When a preferred template map exists, $cat_field_id is overridden and $join_on becomes the same
                if ( array_key_exists($join_on, $seen_join_on) ) continue;
                $seen_join_on[$join_on] = 1;

                $year_ext = $mt->db()->apply_extract_date('year', $dt_target_col);

                $inside = $ctx->stash('inside_archive_list');
                if (!isset($inside)) {
                  $inside = false;
                }
                if ($inside) {
                    $ts = $ctx->stash('current_timestamp');
                    $tsend = $ctx->stash('current_timestamp_end');
                    if ($ts && $tsend) {
                        $ts = $mt->db()->ts2db($ts);
                        $tsend = $mt->db()->ts2db($tsend);
                        $date_filter = "and $dt_target_col between '$ts' and '$tsend'";
                    }
                }

                $sql = implode(' ', array(
                    "select count(*) as cd_count, $year_ext as y, $cat_target_col as category_id, category_label",
                    'from mt_cd',
                    $join_on,
                    "where cd_blog_id = $blog_id and cd_status = 2",
                    isset($date_filter) ? $date_filter : '',
                    $content_type_filter,
                    "group by $year_ext, $cat_target_col, category_label",
                    "order by category_label $cat_order, $year_ext $order"
                ));
                $limit = isset($args['lastn']) ? $args['lastn'] : -1;
                $offset = isset($args['offset']) ? $args['offset'] : -1;
                $results = $mt->db()->SelectLimit($sql, $limit, $offset);
                if (!empty($results)) {
                    $array = $results->GetArray();
                    $categories = array_merge($categories, $array);
                }
            }
        }
        return $categories;
    }

    public function get_template_params() {
        $array = parent::get_template_params();
        $array['category_fiscal_yearly_archive']  = 1;
        $array['datebased_fiscal_yearly_archive'] = 1;
        $array['archive_class']                   = 'contenttype-category-fiscal-yearly-archive';
        return $array;
    }

    public function template_params() {
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        $vars =& $ctx->__stash['vars'];
        $vars += ContentTypeCategoryFiscalYearlyArchiver::get_template_params();
    }

    protected function get_helper() {
        return 'start_end_fiscal_year';
    }

    protected function get_update_link_args($results) {
        $args = array();
        if (!empty($result)) {
            $count = count($results);
            
            $args['hi'] = sprintf("%04d0000000000", $results[0]['y']);
            $args['low'] = sprintf("%04d0000000000", $results[$count - 1]['y']);
        }
        return $args;
    }
}

function start_end_fiscal_year($ts) {
    $y = intval(substr($ts, 0, 4));
    $mo = intval(substr($ts, 4, 2));

    $mt = MT::get_instance();
    $ctx =& $mt->context();
    $blog_id = $ctx->stash('blog_id');
    $config = $ctx->mt->db()->fetch_plugin_data('FiscalYearlyArchives', "configuration:blog:$blog_id");

    $start_mo = intval($config['starting_month']);
    if ( $start_mo === 0 ) $start_mo = 4;

    $start_y = $mo && $mo < $start_mo ? $y - 1 : $y;
    $end_y = $start_mo === 1 ? $y : $y + 1;
    $end_mo = $start_mo === 1 ? 12 : $start_mo - 1;

    $start = sprintf("%04d%02d01000000", $start_y, $start_mo);
    $end = sprintf("%04d%02d%02d235959", $end_y, $end_mo, days_in($end_mo, $end_y));

    return array($start, $end);
}

?>
