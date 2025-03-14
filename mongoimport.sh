csvtojson worldcities.csv > worldcities.json
csvtojson Countries-Continents.csv > countries-continents.json

mongoimport --db AnalyseGeo --collection import_villes --file worldcities.json --jsonArray
mongoimport --db AnalyseGeo --collection pays_continent --file countries-continents.json --jsonArray
