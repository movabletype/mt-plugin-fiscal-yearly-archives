# Movable Type (r) (C) 2001-2019 Six Apart Ltd. All Rights Reserved.
# This code cannot be redistributed without permission from www.sixapart.com.
# For more information, consult your Movable Type license.
#
# $Id:$

package FiscalYearlyArchives::L10N::ja;
use strict;
use warnings;
use utf8;
use MT::L10N;
use MT::L10N::en_us;
use vars qw( @ISA %Lexicon );
@ISA = qw( MT::L10N::en_us );

%Lexicon = (
    'FISCAL-YEARLY_ADV' => '年度別',
    'CONTENTTYPE-FISCAL-YEARLY_ADV' => 'コンテンツタイプ 年度別',
    'The fiscal yearly archive is added. Starting month can be specificated every site. Default starting month is April.' => '年度別アーカイブを追加します。開始月はサイトごとに指定できます。デフォルトの開始月は4月です。',
    'Please enter starting month for the fiscal yearly archive.' => '年度別アーカイブの開始月を入力してください。',
    'Starting Month' => '開始月',
    'January' => '1月',
    'February' => '2月',
    'March' => '3月',
    'April' => '4月',
    'May' => '5月',
    'June' => '6月',
    'July' => '7月',
    'August' => '8月',
    'September' => '9月',
    'October' => '10月',
    'November' => '11月',
    'December' => '12月',
);

1;
