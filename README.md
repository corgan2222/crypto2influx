# crypto2influx
 Making an awesome dashboard for Nanopool ETH and Bitcoin
 original from 
  https://blog.haschek.at/2017/making-an-awesome-dashboard-for-your-crypto.html

new Features:
  - add Worker Hasrate
  - add nanopool actuall balance
  - add nanopool actuall balance euro
  - fix BTC error
  - fix Approx mining earnings per Month
 

 # Influx Setup
 
 edit 
 
 ```bash
/etc/influxdb/influxdb.conf
 ```
 
```bash
[[udp]]
  enabled = true
  bind-address = ":8072"
  database = "coinsworth"
  retention-policy = ""
  batch-size = 10
  batch-pending = 5
  read-buffer = 0
  batch-timeout = "10s"
  precision = ""
 ```


 This opens up UDP port 8072 and binds it to (and automatically creates) the db called "coinsworth" which will hold all data for your dashboard. Batch timeout 10s means that the values you send to InfluxDB will be held in RAM for 10 seconds or until 10 data values are submitted before writing it to the disk.

 for more installation infos goto https://blog.haschek.at/2017/making-an-awesome-dashboard-for-your-crypto.html 
 
 ![grafik](https://user-images.githubusercontent.com/12233951/138332088-0baea99c-f47d-406a-b1f1-884cb76ea2ea.png)
