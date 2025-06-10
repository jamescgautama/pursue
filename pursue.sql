CREATE TABLE talents (
    talent_id       INT NOT NULL AUTO_INCREMENT,
    talent_email    VARCHAR(255) UNIQUE NOT NULL,
    talent_password VARCHAR(255) NOT NULL,
    PRIMARY KEY (talent_id)
);

CREATE TABLE projects (
    project_id      INT NOT NULL AUTO_INCREMENT,
    project_email   VARCHAR(255) UNIQUE NOT NULL,
    project_password VARCHAR(255) NOT NULL,
    PRIMARY KEY (project_id)

);

CREATE TABLE admins (
    admin_id       INT NOT NULL AUTO_INCREMENT,
    admin_email    VARCHAR(255) UNIQUE NOT NULL,
    admin_password VARCHAR(255) NOT NULL,
    PRIMARY KEY (admin_id)
);


CREATE TABLE talent_profiles (
    talent_id      INT NOT NULL AUTO_INCREMENT,
    talent_name    VARCHAR(255),
    bio            TEXT,
    location       VARCHAR(255),
    cv_url         VARCHAR(255),
    FOREIGN KEY (talent_id) REFERENCES talents(talent_id)
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id)
);

CREATE TABLE project_profiles (
    project_id     INT NOT NULL AUTO_INCREMENT,
    project_name   VARCHAR(255),
    description    TEXT,
    website_url    VARCHAR(255),
    location       VARCHAR(255),
    FOREIGN KEY (project_id) REFERENCES projects(project_id)
);

CREATE TABLE talent_skills (
    skill_id       INT NOT NULL AUTO_INCREMENT,
    talent_id      INT NOT NULL,
    PRIMARY KEY (skill_id, talent_id),
    FOREIGN KEY (talent_id) REFERENCES talents(talent_id),
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id),
)

CREATE TABLE skills (
    skill_id       INT NOT NULL AUTO_INCREMENT,
    skill_name     VARCHAR(255) UNIQUE NOT NULL,
    PRIMARY KEY (skill_id)
);

CREATE TABLE project_industries (
    industry_id    INT NOT NULL AUTO_INCREMENT,
    project_id     INT NOT NULL,
    PRIMARY KEY (industry_id, project_id),
    FOREIGN KEY (project_id) REFERENCES projects(project_id)
    FOREIGN KEY (industry_id) REFERENCES industries(industry_id)
);

CREATE TABLE industries (
    industry_id    INT NOT NULL AUTO_INCREMENT,
    industry_name  VARCHAR(255) UNIQUE NOT NULL,
    PRIMARY KEY (industry_id)
);

CREATE TABLE listings (
    listing_id     INT AUTO_INCREMENT PRIMARY KEY;
    project_id     INT,
    job_title      VARCHAR(255), 
    company_name   VARCHAR(255), 
    description    TEXT,
    location       VARCHAR(255),
    salary         VARCHAR(255),
    date_posted    DATE NOT NULL DEFAULT CURDATE(),
    job_type       VARCHAR(255),
    category       VARCHAR(255),
    admin_id        INT,
    approval       INT NOT NULL DEFAULT 0
);

CREATE TABLE applications (
    application_id  INT NOT NULL AUTO_INCREMENT,
    listing_id      INT NOT NULL,
    talent_id       INT NOT NULL,
    application_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'Pending',
    PRIMARY KEY (application_id)
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id),
    FOREIGN KEY (talent_id) REFERENCES talents(talent_id)
);

