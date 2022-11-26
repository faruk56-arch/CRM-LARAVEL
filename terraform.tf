terraform {

cloud {
    organization = "test_terrraform"

    workspaces {
      name = "CRM-LARAVEL"
    }
  }

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 3.28.0"
    }
  }

required_version = ">= 1.1.0"
}