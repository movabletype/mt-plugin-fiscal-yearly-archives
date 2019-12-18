# Movable Type (r) (C) 2001-2019 Six Apart Ltd. All Rights Reserved.
# This code cannot be redistributed without permission from www.sixapart.com.
# For more information, consult your Movable Type license.
#
# $Id$

package FiscalYearlyArchives::Util;

use strict;
use warnings;
use utf8;
use base 'Exporter';
use Time::Local;

our @EXPORT_OK = qw( start_end_fiscal_year );

sub start_end_fiscal_year {
    my $ts = shift;
    my ( $y, $m ) = unpack 'A4A2', $ts;

    my $app    = MT->instance;
    my $plugin = MT->component("FiscalYearlyArchives");
    my $config = $plugin->get_config_hash( 'blog:' . $app->blog->id );

    my $starting_month = $config->{starting_month};

    $y-- if $m < $starting_month;

    my $start = sprintf "%04d%02d01000000", $y, $starting_month;
    return $start unless wantarray;

    my $timelocal = timelocal( 0, 0, 0, 1, $starting_month - 1, $y - 1900 );
    my $endofyear = $timelocal + 365 * 24 * 3600;
    my ( $day, $month, $year ) = ( localtime($endofyear) )[ 3 .. 5 ];
    my $end = sprintf "%04d%02d%02d235959", $year + 1900, $month + 1, $day;

    ( $start, $end );
}

1;
