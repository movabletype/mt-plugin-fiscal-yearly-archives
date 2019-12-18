# Movable Type (r) (C) 2001-2019 Six Apart Ltd. All Rights Reserved.
# This code cannot be redistributed without permission from www.sixapart.com.
# For more information, consult your Movable Type license.
#
# $Id$

package FiscalYearlyArchives::FiscalYearly;

use strict;
use warnings;
use base qw( MT::ArchiveType::Date );
use FiscalYearlyArchives::Util qw( start_end_fiscal_year );

sub name {
    return 'Fiscal-Yearly';
}

sub archive_label {
    my $plugin = MT->component("FiscalYearlyArchives");
    return $plugin->translate("FISCAL-YEARLY_ADV");
}

sub order {
    return 65;
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
        datebased_only_archive   => 1,
        datebased_yearly_archive => 1,
        module_yearly_archives   => 1,
        archive_template         => 1,
        archive_listing          => 1,
        archive_class            => "fiscal-yearly-archive",
        datebased_archive        => 1,
    };
}

sub archive_file {
    my $obj = shift;
    my ( $ctx, %param ) = @_;
    my $timestamp = $param{Timestamp};
    my $file_tmpl = $param{Template};
    my $blog      = $ctx->{__stash}{blog};

    my $file;
    if ($file_tmpl) {
        ( $ctx->{current_timestamp}, $ctx->{current_timestamp_end} )
            = start_end_fiscal_year( $timestamp, $blog );
    }
    else {
        my $start = start_end_fiscal_year( $timestamp, $blog );
        my ($year) = unpack 'A4', $start;
        $file = sprintf( "fy/%04d/index", $year );
    }

    $file;
}

sub archive_title {
    my $obj = shift;
    my ( $ctx, $entry_or_ts ) = @_;
    my $stamp = ref $entry_or_ts ? $entry_or_ts->authored_on : $entry_or_ts;
    my $start = start_end_fiscal_year( $stamp, $ctx->stash('blog') );
    require MT::Template::Context;
    my $year = MT::Template::Context::_hdlr_date( $ctx,
        { ts => $start, 'format' => "%Y" } );
    my $lang = lc MT->current_language || 'en_us';
    $lang = 'ja' if lc($lang) eq 'jp';

    sprintf( "%s%s%s",
        ( $lang ne 'ja' ? 'FY' : '' ),
        $year, ( $lang eq 'ja' ? '&#24180;&#24230;' : '' ) );
}

sub date_range {
    my $obj = shift;
    start_end_fiscal_year(@_);
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

    require MT::Entry;
    $iter = MT::Entry->count_group_by(
        {   blog_id => $blog->id,
            status  => MT::Entry::RELEASE(),
            ( $ts && $tsend ? ( authored_on => [ $ts, $tsend ] ) : () ),
        },
        {   ( $ts && $tsend ? ( range_incl => { authored_on => 1 } ) : () ),
            group => [
                "extract(year from authored_on) AS year",
                "extract(month from authored_on) AS month"
            ],
            sort => [
                {   column => "extract(year from authored_on)",
                    desc   => $order
                },
                {   column => "extract(month from authored_on)",
                    desc   => $order
                }
            ],
        }
    ) or return $ctx->error("Couldn't get fiscal yearly archive list");

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

    my @limited_rows
        = $args->{lastn}
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

sub archive_group_entries {
    my $obj = shift;
    my ( $ctx, %param ) = @_;
    my $ts
        = $param{year}
        ? sprintf( "%04d%02d%02d000000", $param{year}, 1, 1 )
        : undef;
    my $limit = $param{limit};
    $obj->dated_group_entries( $ctx, 'FiscalYearly', $ts, $limit );
}

1;
