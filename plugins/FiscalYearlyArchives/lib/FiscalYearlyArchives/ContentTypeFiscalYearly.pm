# Movable Type (r) (C) 2001-2020 Six Apart Ltd. All Rights Reserved.
# This code cannot be redistributed without permission from www.sixapart.com.
# For more information, consult your Movable Type license.
#
# $Id$

package FiscalYearlyArchives::ContentTypeFiscalYearly;

use strict;
use warnings;
use base qw( MT::ArchiveType::ContentTypeDate FiscalYearlyArchives::FiscalYearly );

use FiscalYearlyArchives::Util qw( start_end_fiscal_year );

sub name {
    return 'ContentType-Fiscal-Yearly';
}

sub archive_label {
    my $plugin = MT->component("FiscalYearlyArchives");
    return $plugin->translate("CONTENTTYPE-FISCAL-YEARLY_ADV");
}

sub archive_short_label {
    my $plugin = MT->component("FiscalYearlyArchives");
    return $plugin->translate("FISCAL-YEARLY_ADV");
}

sub order {
    return 215;
}

sub dynamic_template {
    return 'archives/<$MTArchiveDate format="%Y"$>';
}

sub default_archive_templates {
    my $plugin = MT->component("FiscalYearlyArchives");
    return [
        {   label    => $plugin->translate('fy/yyyy/index.html'),
            template => 'fy/%y/%i',
            default  => 1
        },
        {   label    => $plugin->translate('fy-yyyy/index.html'),
            template => 'fy-%y/%i',
        },
        {   label    => $plugin->translate('fy_yyyy/index.html'),
            template => 'fy_%y/%i',
        },
        {   label    => $plugin->translate('fy/yyyy.html'),
            template => 'fy/%y.html',
        },
        {   label    => $plugin->translate('fy-yyyy.html'),
            template => 'fy-%y.html',
        },
        {   label    => $plugin->translate('fy_yyyy.html'),
            template => 'fy_%y.html',
        },
    ];
}

sub template_params {
    return {
        archive_class               => "contenttype-fiscal-yearly-archive",
        datebased_yearly_archive    => 1,
        module_yearly_archives      => 1,
        archive_template            => 1,
        archive_listing             => 1,
        datebased_archive           => 1,
        datebased_only_archive      => 1,
        contenttype_archive_listing => 1,
    };
}

sub archive_group_iter {
    my $obj = shift;
    my ( $ctx, $args ) = @_;
    my $blog = $ctx->stash('blog');
    my $iter;
    my $sort_order
        = ( $args->{sort_order} || '' ) eq 'ascend' ? 'ascend' : 'descend';
    my $order = ( $sort_order eq 'ascend' ) ? 'asc' : 'desc';

    my $ts    = $ctx->{current_timestamp};
    my $tsend = $ctx->{current_timestamp_end};

    my $content_type_id = $ctx->stash('content_type')->id;
    my $map             = $obj->_get_preferred_map(
        {   blog_id         => $blog->id,
            content_type_id => $content_type_id,
            map             => $ctx->stash('template_map'),
        }
    );
    my $dt_field_id = $map ? $map->dt_field_id : '';

    require MT::ContentData;
    require MT::ContentFieldIndex;

    my $group_terms
        = $obj->make_archive_group_terms( $blog->id, $dt_field_id, $ts,
        $tsend, '', $content_type_id );
    my $group_args
        = $obj->make_archive_group_args( 'datebased_only', 'monthly', $map,
        $ts, $tsend, $args->{lastn}, $order, '' );

    $iter = MT::ContentData->count_group_by( $group_terms, $group_args )
        or return $ctx->error("Couldn't get ContentType fiscal yearly archive list");

    my %counts;
    while ( my @row = $iter->() ) {
        my $date = sprintf( "%04d%02d%02d000000", $row[1], $row[2], 1 );
        my ( $start, $end ) = start_end_fiscal_year($date);
        my $fiscal_year = substr( $start, 0, 4 );
        if ( $counts{$fiscal_year} ) {
            $counts{$fiscal_year}{count} += $row[0];
        }
        else {
            $counts{$fiscal_year} = {
                count => $row[0],
                start => $start,
                end   => $end,
            };
        }
    }
    my @rows = map {
        {   count => $counts{$_}{count},
            year  => $counts{$_},
            start => $counts{$_}{start},
            end   => $counts{$_}{end}
        }
        } ( $args->{sort_order} || '' ) eq 'ascend'
        ? sort keys %counts
        : reverse sort keys %counts;

    my @limited_rows = $args->{lastn}
        ? splice @rows, 0, $args->{lastn}
        : @rows;

    return sub {
        while ( my $row = shift(@limited_rows) ) {
            return (
                $row->{count},
                year  => $row->{year},
                start => $row->{start},
                end   => $row->{end}
            );
        }
        undef;
    };
}

sub archive_group_contents {
    my $obj = shift;
    my ( $ctx, $param, $content_type_id ) = @_;
    my $ts
        = $param->{year}
        ? sprintf( "%04d%02d%02d000000", $param->{year}, 1, 1 )
        : undef;
    my $limit = $param->{limit};
    $obj->dated_group_contents( $ctx, $obj->name, $ts, $limit,
        $content_type_id );
}

*date_range    = \&FiscalYearlyArchives::FiscalYearly::date_range;
*archive_file  = \&FiscalYearlyArchives::FiscalYearly::archive_file;
*archive_title = \&FiscalYearlyArchives::FiscalYearly::archive_title;

1;
