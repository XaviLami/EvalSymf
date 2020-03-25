<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produits;
use App\Form\AddPanierType;
use App\Form\AddProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
  /**
   * @Route("/produits", name="produits")
   */
  public function index(Request $request, EntityManagerInterface $entityManager)
  {
    $produit = new Produits();
    $produitRepository = $this->getDoctrine()->getRepository(Produits::class)->findAll();

    $form = $this->createForm(AddProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $produit = $form->getData();

      $photo = $produit->getPhoto();
      $photoName = md5(uniqid()) . '.' . $photo->guessExtension();
      $photo->move($this->getParameter('upload_files'), $photoName);
      $produit->setPhoto($photoName);

      $entityManager->persist($produit);
      $entityManager->flush();

      return $this->redirectToRoute('panier');


    }

    return $this->render('main/index.html.twig', [
      'produits' => $produitRepository,
      'addProduit' => $form->createView()
    ]);

  }

  /**
   * @Route("/fiche/produit/{id}", name="ficheProduit")
   */

  public function ficheProduit($id,EntityManagerInterface $entityManager, Request $request)
  {
    $singleProduit = $this->getDoctqrine()->getRepository(Produits::class)->find($id);

    $paniers= new Panier();

    $panierRepository=$this->getDoctrine()->getRepository(Panier::class)->findAll();

    $form = $this->createForm(AddPanierType::class, $paniers);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
    $paniers = $form->getData();

    $paniers->setDateAjout(new \DateTime());
    $paniers->setEtat(false);
    $paniers->setProduit($singleProduit);

    $entityManager->persist($paniers);
    $entityManager->flush();

      return $this->redirectToRoute('panier');


    }
    return $this->render('main/ficheProduit.html.twig', [
      'produit' => $singleProduit,
      'paniers'=>$panierRepository,
      'formPanier'=>$form->createView()

    ]);

  }

  /**
   * @Route("/produit/remove/{id}", name="removeProduit")
   */
  public function removeProduit($id, EntityManagerInterface $entityManager)
  {
    $produit = $this->getDoctrine()->getRepository(Produits::class)->find($id);

    $entityManager->remove($produit);
    $entityManager->flush();

    return $this->redirectToRoute('produits');
  }
  /**
   * @Route("/useless", name="panier")
   */
  public function acceuil()
  {
    $paniers=$this->getDoctrine()->getRepository(Panier::class)->findAll();

    $panierTotal = 0;

    foreach ($paniers as $panier){
      $panierTotal += ($panier->getProduit()->getPrix() * $panier->getQuantiteCommande());
    }

    return $this->render('main/panier.html.twig', [
      'paniers' => $paniers,
      'panierTotal'=>$panierTotal

    ]);
  }
  /**
   * @Route("/panier/remove/{id}", name="removePanier")
   */
  public function removePanier($id, EntityManagerInterface $entityManager)
  {
    $paniers = $this->getDoctrine()->getRepository(Panier::class)->find($id);

    $entityManager->remove($paniers);
    $entityManager->flush();

    return $this->redirectToRoute('panier');
  }


}
