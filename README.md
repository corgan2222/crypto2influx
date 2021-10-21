# crypto2influx
 Making an awesome dashboard for Nanopool ETH and Bitcoin
 original from 
  https://blog.haschek.at/2017/making-an-awesome-dashboard-for-your-crypto.html

new Features:
- add worker hashrates
- fix bitcoin api

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