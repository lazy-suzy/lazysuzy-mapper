#!/usr/bin/expect -f
set timeout 120

#remove previous file
system "rm -f /home/ec2-user/mapper/csv/Cost*"

spawn sftp 5184629@datatransfer.cj.com
expect "*?assword:*"
send -- "+VjGrgvY\r"
sleep 2
send -- "get /outgoing/productcatalog/234843/Cost_Plus_World_Market-Cost_Plus_World_Market_Google_Feed-shopping.txt.zip /home/ec2-user/mapper/csv/\r"
sleep 15
send -- "exit\r"

spawn unzip /home/ec2-user/mapper/csv/Cost_Plus_World_Market-Cost_Plus_World_Market_Google_Feed-shopping.txt.zip -d /home/ec2-user/mapper/csv/
expect eof
