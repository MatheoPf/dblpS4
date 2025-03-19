
create schema if not exists AnalyseGeo;
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
COPY AnalyseGeo.import_villes
FROM '/docker-entrypoint-initdb.d/worldcities.csv'
WITH (FORMAT csv, HEADER true, DELIMITER ',');

-- Insertion de import_villes à villes
insert into AnalyseGeo._villes (id, nom_ville, latitude, longitude, iso, nom_pays)
select distinct id, city_ascii, lat, lng, iso3, country
from AnalyseGeo.import_villes
on conflict (id) do nothing;

-- copy
COPY AnalyseGeo.pays_continent (continent, pays)
FROM '/docker-entrypoint-initdb.d/Countries-Continents.csv'
WITH (DELIMITER ',', FORMAT csv, HEADER true);

-- Supprime la table temporaire
drop table AnalyseGeo.import_villes;

  
create or replace view AnalyseGeo.touteslesinfos as (
  select * from AnalyseGeo._villes inner join AnalyseGeo.pays_continent on pays = nom_pays);


-- Création table publication
create table if not exists AnalyseGeo._publications (
  id_dblp VARCHAR(50) PRIMARY KEY,
  type VARCHAR(255),
  doi VARCHAR(255),
  titre VARCHAR(255),
  lieu VARCHAR(255),
  annee INTEGER,
  pages VARCHAR(50),
  ee VARCHAR(255),
  url_dblp VARCHAR(255)
);
-- drop table AnalyseGeo._publications;

-- Création table revues
CREATE TABLE if not exists AnalyseGeo._revues (
  volume VARCHAR(255),
  numero INTEGER
) inherits (AnalyseGeo._publications);
ALTER TABLE AnalyseGeo._revues ADD PRIMARY KEY (id_dblp);
-- drop table AnalyseGeo._revues

CREATE TABLE if not exists AnalyseGeo._conferences (
  -- les données seront considérées plus tard
) inherits (AnalyseGeo._publications);
ALTER TABLE AnalyseGeo._conferences ADD PRIMARY KEY (id_dblp);
-- drop table AnalyseGeo._conférences

-- Création table auteurs
CREATE TABLE if not exists AnalyseGeo._auteurs (
  pid VARCHAR(50),
  orc_id VARCHAR(20),
  hal_id VARCHAR(255),
  nom VARCHAR(255)
);
ALTER TABLE AnalyseGeo._auteurs ADD PRIMARY KEY (pid);
ALTER TABLE AnalyseGeo._auteurs ADD CONSTRAINT unique_hal_id UNIQUE (hal_id);

-- drop table AnalyseGeo._auteurs;

-- Création table a_ecrit en lien avec les tables auteurs et publications
CREATE TABLE if not exists AnalyseGeo.a_ecrit (
  id_dblp VARCHAR(50),
  pid VARCHAR(50),
  ordre INT
);
ALTER TABLE AnalyseGeo.a_ecrit ADD PRIMARY KEY (id_dblp, pid);
ALTER TABLE AnalyseGeo.a_ecrit ADD FOREIGN KEY (id_dblp) REFERENCES AnalyseGeo._publications(id_dblp);
ALTER TABLE AnalyseGeo.a_ecrit ADD FOREIGN KEY (pid) REFERENCES AnalyseGeo._auteurs(pid);
-- drop table AnalyseGeo.a_ecrit;

-- Création table _structures
CREATE TABLE if not exists AnalyseGeo._structures (
	id_lab integer,
	acronyme varchar(50),
	nom_lab varchar(255),
	id_adresse int
);
ALTER TABLE AnalyseGeo._structures ADD PRIMARY KEY (id_lab);
ALTER TABLE AnalyseGeo._structures ADD CONSTRAINT unique_id_adresse UNIQUE (id_adresse);

-- drop table AnalyseGeo._structures;

-- Création table _adresses en lien avec les table _structures et _villes
CREATE TABLE if not exists AnalyseGeo._adresses (
	id_adresse serial,
	cp int,
  rue varchar(255),
	nom_ville varchar(100)
);
ALTER TABLE AnalyseGeo._adresses ADD PRIMARY KEY (id_adresse);
ALTER TABLE AnalyseGeo._adresses ADD FOREIGN KEY (id_adresse) REFERENCES AnalyseGeo._structures(id_adresse);
-- drop table AnalyseGeo._adresses;

-- Création table _est_affilie en lien avec les table _structures et _auteurs
CREATE TABLE if not exists AnalyseGeo._est_affilie (
    hal_id VARCHAR(50),
    id_lab INTEGER
);
ALTER TABLE AnalyseGeo._est_affilie ADD PRIMARY KEY (hal_id, id_lab);
ALTER TABLE AnalyseGeo._est_affilie ADD FOREIGN KEY (hal_id) REFERENCES AnalyseGeo._auteurs(hal_id) ON DELETE CASCADE;
ALTER TABLE AnalyseGeo._est_affilie ADD FOREIGN KEY (id_lab) REFERENCES AnalyseGeo._structures(id_lab) ON DELETE CASCADE;
-- drop table AnalyseGeo._est_affilie;