use strict;
use DBI;

my $filename = 'maillog';
open(my $fh, '<', $filename) or die "Не могу открыть '$filename' $!";

my $mysql_dbh = DBI->connect( "dbi:mysql:host=127.0.0.1;" .
                "user=test_user;" .
                'password=password;'  .
                "database=test_DB");
                
my $message_isert = $mysql_dbh->prepare("INSERT INTO message (created,id,int_id,str) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE id=?");             
my $log_isert = $mysql_dbh->prepare("INSERT INTO log (created,int_id,str,address) VALUES (?,?,?,?)");                
                
                
while (my $row = <$fh>) {
    chomp $row;
    my ($timestamp,$str,$int_id,$flag) = ($1,$2,$3,$4) if $row =~ /(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}) ((\w+-\w+-\w+) (=>|<=|==|->|\*\*)?.*)/;
    next if (not $int_id);
    if ($flag eq "<="){
        my $id = $1 if $row =~ /id=(.+)/;
		next if (not $id);
        $message_isert->execute($timestamp,$id,$int_id,$str,$id);
    }else{
        my $address = $2 if $row =~ /(=>|==|->|\*\*) ([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/;
        $log_isert->execute($timestamp,$int_id,$str,$address);
    }
}
close $fh;
$mysql_dbh->disconnect;

1;