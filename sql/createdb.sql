create schema if not exists AnalyseGeo;
set schema 'AnalyseGeo';

-- Table import_villes (temporaire)
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

-- Table _villes
create table if not exists AnalyseGeo._villes (
    id integer not null primary key,
    nom_ville varchar(50) not null,
    latitude float not null,
    longitude float not null,
    iso char(4) not null,
    nom_pays varchar(50) not null
);
-- drop table AnalyseGeo._villes;

-- Table pays_continent
create table if not exists AnalyseGeo.pays_continent(
  continent varchar(15),
  pays varchar(50)
);
-- drop table AnalyseGeo.pays_continent;

-- Chargement et insertion des données
COPY AnalyseGeo.import_villes
FROM '/docker-entrypoint-initdb.d/worldcities.csv'
WITH (FORMAT csv, HEADER true, DELIMITER ',');

insert into AnalyseGeo._villes (id, nom_ville, latitude, longitude, iso, nom_pays)
select distinct id, city_ascii, lat, lng, iso3, country
from AnalyseGeo.import_villes
on conflict (id) do nothing;

COPY AnalyseGeo.pays_continent (continent, pays)
FROM '/docker-entrypoint-initdb.d/Countries-Continents.csv'
WITH (DELIMITER ',', FORMAT csv, HEADER true);

drop table AnalyseGeo.import_villes;

create or replace view AnalyseGeo.touteslesinfos as (
  select * from AnalyseGeo._villes inner join AnalyseGeo.pays_continent on pays = nom_pays
);

-- Table publications
create table if not exists AnalyseGeo._publications (
  id_dblp VARCHAR(255) PRIMARY KEY,
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

-- Table revues
CREATE TABLE if not exists AnalyseGeo._revues (
  volume VARCHAR(255),
  numero INTEGER
) inherits (AnalyseGeo._publications);
ALTER TABLE AnalyseGeo._revues ADD PRIMARY KEY (id_dblp);
-- drop table AnalyseGeo._revues;

-- Table conferences
CREATE TABLE if not exists AnalyseGeo._conferences (
  -- données à intégrer ultérieurement
) inherits (AnalyseGeo._publications);
ALTER TABLE AnalyseGeo._conferences ADD PRIMARY KEY (id_dblp);
-- drop table AnalyseGeo._conferences;

-- Table auteurs (supprimée la colonne hal_id)
CREATE TABLE if not exists AnalyseGeo._auteurs (
  pid VARCHAR(255),
  orc_id VARCHAR(255),
  nom VARCHAR(255)
);
ALTER TABLE AnalyseGeo._auteurs ADD PRIMARY KEY (pid);
-- drop table AnalyseGeo._auteurs;

-- Table a_ecrit : relation entre publications et auteurs
CREATE TABLE if not exists AnalyseGeo.a_ecrit (
  id_dblp VARCHAR(255),
  pid VARCHAR(255),
  ordre INT
);
ALTER TABLE AnalyseGeo.a_ecrit ADD PRIMARY KEY (id_dblp, pid);
ALTER TABLE AnalyseGeo.a_ecrit ADD FOREIGN KEY (id_dblp) REFERENCES AnalyseGeo._publications(id_dblp);
ALTER TABLE AnalyseGeo.a_ecrit ADD FOREIGN KEY (pid) REFERENCES AnalyseGeo._auteurs(pid);
-- drop table AnalyseGeo.a_ecrit;

-- Table adresses
CREATE TABLE if not exists AnalyseGeo._adresses (
  id_adresse serial,
  cp int,
  rue varchar(255),
  nom_ville varchar(100)
);
ALTER TABLE AnalyseGeo._adresses ADD PRIMARY KEY (id_adresse);
-- drop table AnalyseGeo._adresses;

-- Table structures
CREATE TABLE if not exists AnalyseGeo._structures (
  id_struct VARCHAR(50),
  ror VARCHAR(255),
  acronyme varchar(50),
  nom_struct varchar(255),
  id_adresse int
);
ALTER TABLE AnalyseGeo._structures ADD PRIMARY KEY (id_struct);
ALTER TABLE AnalyseGeo._structures ADD CONSTRAINT unique_id_adresse UNIQUE (id_adresse);
ALTER TABLE AnalyseGeo._structures ADD FOREIGN KEY (id_adresse) REFERENCES AnalyseGeo._adresses(id_adresse);
-- drop table AnalyseGeo._structures;

-- Nouvelle table affiliation : relation entre auteur et structure (affiliation)
CREATE TABLE if not exists AnalyseGeo._affiliation (
    pid VARCHAR(50),
    id_struct VARCHAR(50),
    PRIMARY KEY (pid, id_struct),
    FOREIGN KEY (pid) REFERENCES AnalyseGeo._auteurs(pid) ON DELETE CASCADE,
    FOREIGN KEY (id_struct) REFERENCES AnalyseGeo._structures(id_struct) ON DELETE CASCADE
);
-- drop table AnalyseGeo._affiliation;

-- Table lineage : lie une structure à ses institutions parentes
CREATE TABLE IF NOT EXISTS AnalyseGeo._lineage (
  id_struct VARCHAR(50),
  parent_lab VARCHAR(50),
  position INT,
  PRIMARY KEY (id_struct, parent_lab),
  FOREIGN KEY (id_struct) REFERENCES AnalyseGeo._structures(id_struct) ON DELETE CASCADE
);
-- drop table AnalyseGeo._lineage;