<?php

function getAllProducts(){
  $sql ="SELECT id,title,description,price,slug,status
  FROM products";
  if(false === isAdmin()){
    $sql .= " WHERE status = 'LIVE'";
  }

  $result = getDB()->query($sql);
  if(!$result){
    return [];
  }
  $products = [];
  while($row = $result->fetch()){
    $row['mainImage']=getProductMainImage($row['slug']);
    $products[]=$row;
  }
 
  return $products;
}

function getProductBySlug(string $slug):?array{
  $sql ="SELECT id,title,description,price,slug,status
  FROM products
  WHERE slug=:slug
  LIMIT 1
  ";
  $statement = getDB()->prepare($sql);
  if(false === $statement){
    return null;
  }
  $statement->execute(
    [':slug'=>$slug]
  );
  if($statement->rowCount() === 0){
    return null;
  }
  $product = $statement->fetch();
  $product['mainImage']=getProductMainImage($product['slug']);
  return $product;
}
function editProduct(int $id, string $productName,string $slug,string $description,int $price):bool{
  $sql="UPDATE products SET 
  title = :productName,
  slug = :slug,
  description = :description,
  price = :price
  WHERE id= :id
  ";
    $statement = getDB()->prepare($sql);
    if(false === $statement){
      return false;
    }
    $statement->execute(
      [
        ':id'=>$id,
        ':productName' => $productName,
        ':slug'=>$slug,
        ':description'=>$description,
        ':price'=>$price,
      ]
    ); 
    $rowCount = $statement->rowCount();
    return $rowCount >= 0;
}
function createProduct(string $productName,string $slug,string $description,int $price):bool{
    $sql="INSERT INTO products SET 
    title = :productName,
    slug = :slug,
    description = :description,
    price = :price
    ";
      $statement = getDB()->prepare($sql);
      if(false === $statement){
        return false;
      }
      $statement->execute(
        [
          ':productName' => $productName,
          ':slug'=>$slug,
          ':description'=>$description,
          ':price'=>$price,
        ]
      ); 
      $lastId = getDB()->lastInsertId();
      return $lastId > 0;
}
function uploadedPictures(?string $name = null){
  static $pictures = [];
  if(is_null($name)){
    return $pictures;
  }
  $pictures[]=$name;
}
function uploadProductPictures(string $slug,array $picutres):bool{
  $picutrePath = STORAGE_DIR.'/productPictures/'.$slug.'/';
  if(!is_dir($picutrePath)){
    mkdir($picutrePath,0777,true);
  }

  $fileNames = glob($picutrePath.'*');
  $fileName = count($fileNames)+1;

  $filesToCheck= [];
  foreach($picutres as $picutre){
    $filesToCheck[]=$picutrePath.$fileName.'.'.$picutre['extension'];
    copy($picutre['tmp_name'],$picutrePath.$fileName.'.'.$picutre['extension']);
    unlink($picutre['tmp_name']);
    uploadedPictures($slug.'/'.$fileName.'.'.$picutre['extension']);

    $fileName++;
  }
  $result  = true;
  foreach($filesToCheck as $file){
    if(false === is_file($file)){
      $result = false;
    break;
    }
  }
  return $result;
}

function getProductMainImage(string $slug):string{
  $mainImages = glob(STORAGE_DIR.'/productPictures/'.$slug.'/*-main*');
  if(count($mainImages) === 0){
    $mainImages = glob(STORAGE_DIR.'/productPictures/'.$slug.'/1.*');
  }
  return basename($mainImages[0]);
};