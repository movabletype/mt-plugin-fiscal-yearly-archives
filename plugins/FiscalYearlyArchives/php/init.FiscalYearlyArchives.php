<?php
# Movable Type (r) (C) 2001-2019 Six Apart Ltd. All Rights Reserved.
# This code cannot be redistributed without permission from www.sixapart.com.
# For more information, consult your Movable Type license.
#
# $Id$

require_once('archive_lib.php');
require_once('MTUtil.php');

ArchiverFactory::add_archiver('Fiscal-Yearly', 'FiscalYearlyArchiver');
ArchiverFactory::add_archiver('ContentType-Fiscal-Yearly', 'ContentTypeFiscalYearlyArchiver');

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
        $array['datebased_only_archive']   = 1;
        $array['datebased_yearly_archive'] = 1;
        $array['archive_class']            = 'datebased-yearly-archive';
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
        $stamp = $ctx->stash('current_timestamp'); #$entry['entry_authored_on'];
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
        $array['datebased_only_archive']   = 1;
        $array['datebased_yearly_archive'] = 1;
        $array['archive_class']            = 'contenttype-yearly-archive';
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
