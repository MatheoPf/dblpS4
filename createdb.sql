create schema AnalyseGeo;
set schema 'AnalyseGeo';

CREATE TABLE AnalyseGeo.import_villes (
    city VARCHAR(50),
    city_ascii VARCHAR(50),
    lat FLOAT,
    lng FLOAT,
    country VARCHAR(50),
    iso2 CHAR(2),
    iso3 CHAR(3),
    admin_name VARCHAR(255),
    capital VARCHAR(50),
    population varchar(255),
    id INTEGER PRIMARY KEY
);

copy AnalyseGeo.import_villes
from 'D:\iut\Semestre2\Analyse\worldcities.csv'
with (FORMAT csv, header true, delimiter ',');

-- Création de la table _villes
--drop table AnalyseGeo._villes;
create table AnalyseGeo._villes (
	id integer not null primary key,
	nom_ville varchar(50) not null,
	latitude float not null,
	longitude float not null,
	iso char(4) not null,
	nom_pays varchar(50) not null
);

-- Insertion de import_villes à villes
insert into AnalyseGeo._villes (id, nom_ville, latitude, longitude, iso, nom_pays)
select distinct id, city_ascii, lat, lng, iso3, country
from AnalyseGeo.import_villes
on conflict (id) do nothing;

-- Supprime la table temporaire
drop table AnalyseGeo.import_villes;

-- drop table AnalyseGeo.pays_continent;
create table if not exists AnalyseGeo.pays_continent(
  continent varchar(15),
  pays varchar(50)
  );
  
copy AnalyseGeo.pays_continent (continent, pays)
    from 'D:\iut\Semestre2\Analyse\Countries-Continents.csv' WITH DELIMITER ',' CSV HEADER; 

create or replace view AnalyseGeo.touteslesinfo as (
  select * from AnalyseGeo._villes inner join AnalyseGeo.pays_continent on pays = nom_ville);