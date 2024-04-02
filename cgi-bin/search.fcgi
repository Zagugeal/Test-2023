#!~/perl

use strict;
use DBI;
use FCGI;
use CGI;

my $fcgi_request = FCGI::Request();

if ($fcgi_request->Accept() >= 0) {
    
    my $cgi = CGI->new;
    my $mail = $cgi->param('mail') || '';
    my $mysql_dbh = DBI->connect( "dbi:mysql:host=127.0.0.1;" .
                "user=test_user;" .
                'password=password;'  .
                "database=test_DB");
    
    if ($mail ne ''){
        my $sorted_data = $mysql_dbh->selectall_arrayref(
            "SELECT 
                * 
            FROM (
                SELECT
                    str,
                    int_id,
                    created
                FROM message 
                UNION ALL
                SELECT 
                    str,
                    int_id,
                    created 
                FROM log
            ) as U
            WHERE U.str LIKE '%$mail%' 
            ORDER BY U.int_id, U.created DESC;", 
            {Slice=>{}}
        );
        
        print "Content-type: text/html\n\n";
        print "<table id='addresses' border='1' style='width: 100%'>\n";
        print "<thead>
                   <tr>
                       <th onclick='sortTable(0)'>Created</th>
                       <th onclick='sortTable(1)'>Log</th>
                   </tr>
               </thead>";
        my $counter = 0;
        my $beyond_limit;
        foreach my $row (@$sorted_data){
            
            $counter++;
            
            if ($counter == 101){
                $beyond_limit = "<div id='limit' style='text-align: right'>Колличество найденых строк превышает лимит в 100 записей</div>";
                last; 
            }
            
            $row->{str} =~ s/</&lt;/g;
            $row->{str} =~ s/>/&gt;/g;
            
            print ' <tr>
                        <td>' . $row->{created} . '</td>
                        <td>' . $row->{str} . '</td>
                    </tr>';
        }
        
        print "</table>";
        print $beyond_limit;
    }
    
    $mysql_dbh->disconnect;
    
}

1;