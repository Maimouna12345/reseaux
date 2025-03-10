CREATE DATABASE smarttech;

USE smarttech;

CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  password VARCHAR(250)
);

CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  password VARCHAR(250)
);

CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  urlFile  VARCHAR(100),
  sendby INT,
  receivedby INT,
  isConfirmed BOOLEAN DEFAULT FALSE,
  -- Clés étrangères
  FOREIGN KEY (sendby) REFERENCES employees(id),
  FOREIGN KEY (receivedby) REFERENCES clients(id)
);
