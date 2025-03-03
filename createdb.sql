-- create schema AnalyseGeo;
set schema 'AnalyseGeo';

create table if not exists AnalyseGeo.import_villes (
    city varchar(50),
    city_ascii varchar(50),
    lat float,
    lng float,
    country varchar(50),
    iso2 char(2),
    iso3 char(3),
    admin_name varchar(255),
    capital varchar(50),
    population varchar(255),
    id integer primary key
);
-- drop table AnalyseGeo.import_villes;

-- Création de la table _villes
create table if not exists AnalyseGeo._villes (
	id integer not null primary key,
	nom_ville varchar(50) not null,
	latitude float not null,
	longitude float not null,
	iso char(4) not null,
	nom_pays varchar(50) not null
);
--drop table AnalyseGeo._villes;

create table if not exists AnalyseGeo.pays_continent(
  continent varchar(15),
  pays varchar(50)
  );
-- drop table AnalyseGeo.pays_continent;

--	copy
copy AnalyseGeo.import_villes
from 'D:\iut\Semestre2\Analyse\worldcities.csv'
with (format csv, header true, delimiter ',');

-- Insertion de import_villes à villes
insert into AnalyseGeo._villes (id, nom_ville, latitude, longitude, iso, nom_pays)
select distinct id, city_ascii, lat, lng, iso3, country
from AnalyseGeo.import_villes
on conflict (id) do nothing;

-- copy
copy AnalyseGeo.pays_continent (continent, pays)
    from 'D:\iut\Semestre2\Analyse\Countries-Continents.csv' with delimiter ',' csv header; 

-- Supprime la table temporaire
drop table AnalyseGeo.import_villes;

 
create or replace view AnalyseGeo.touteslesinfo as (
  select * from AnalyseGeo._villes inner join AnalyseGeo.pays_continent on pays = nom_pays);