<?php


namespace App\Controller;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Service\FileUploader;



use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Todo;
use App\Entity\Status;


class TodoController extends AbstractController

{
    #[Route("/", name:"todo")]
    public function index(): Response
    {
        $todos = $this->getDoctrine()->getRepository('App:Todo')->findAll();
        return $this->render('todo/index.html.twig', array('todos'=>$todos));
    }


    #[Route("/create", name:"todo_create")]
    public function create(Request $request, FileUploader $fileUploader): Response

    {
    $todo = new Todo;

            $form = $this->createFormBuilder($todo)
            ->add('name', TextType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-bottom:15px')))
            ->add('category', TextType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-bottom:15px')))
            ->add('description', TextareaType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-bottom:15px')))
            ->add('priority', ChoiceType::class, array('choices'=>array('Low'=>'Low', 'Normal'=>'Normal', 'High'=>'High'),'attr' => array('class'=> 'form-control', 'style'=>'margin-botton:15px')))
            ->add('due_date', DateTimeType::class, array('attr' => array('style'=>'margin-bottom:15px')))
            ->add('fk_status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'name',         
            ])
            ->add('pictureUrl', FileType::class, [
                'label' => 'Upload Picture',
    //unmapped means that is not associated to any entity property
                'mapped' => false,
    //not mandatory to have a file
                'required' => false,
    
    
    //in the associated entity, so you can use the PHP constraint classes as validators
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file' ,
                    ])
                ],
            ])
    
                 
            ->add('save', SubmitType::class, array('label'=> 'Create Todo', 'attr' => array('class'=> 'btn-primary', 'style'=>'margin-bottom:15px')))
            ->getForm();
             $form->handleRequest($request);
                 
     if($form->isSubmitted() && $form->isValid()){
       $name = $form['name']->getData();
       $category = $form['category']->getData();
       $description = $form['description']->getData();
       $priority = $form['priority']->getData();
       $due_date = $form['due_date']->getData();
       $now = new \DateTime('now');
       $pictureFile = $form->get('pictureUrl')->getData();
       if ($pictureFile) {
        $pictureFileName = $fileUploader->upload($pictureFile);
        $todo->setPictureUrl($pictureFileName);
        }

       
        $todo->setName($name);
        $todo->setCategory($category);
        $todo->setDescription($description);
        $todo->setPriority($priority);
        $todo->setDueDate($due_date);
        $todo->setCreateDate($now);




        $em = $this->getDoctrine()->getManager();
        $em->persist($todo);
        $em->flush();
        
        $this->addFlash(
                'notice',
                'Todo Added'
                );
       
                   return $this->redirectToRoute('todo');
       
               }
               return $this->render('todo/create.html.twig', array('form' => $form->createView()));

    }


    #[Route("/edit/{id}", name:"todo_edit")]

    public function edit(Request $request, $id): Response
    {
        /* Here we have a variable todo and it will save the result of this search and it will be one result because we search based on a specific id */
        $todo = $this->getDoctrine()->getRepository('App:Todo')->find($id);
        $now = new \DateTime('now');
 
    /* Now when you type createFormBuilder and you will put the variable todo the form will be filled of the data that you already set it */
         $form = $this->createFormBuilder($todo)
         ->add('name', TextType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-botton:15px')))
         ->add('category', TextType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-bottom:15px')))
         ->add('description', TextareaType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-botton:15px')))
         ->add('priority', ChoiceType::class, array('choices'=>array('Low'=>'Low', 'Normal'=>'Normal', 'High'=>'High'),'attr' => array('class'=> 'form-control', 'style'=>'margin-botton:15px')))
         ->add('due_date', DateTimeType::class, array('attr' => array('style'=>'margin-bottom:15px')))
         ->add('pictureUrl', FileType::class, [
            'label' => 'Upload Picture',
//unmapped means that is not associated to any entity property
            'mapped' => false,
//not mandatory to have a file
            'required' => false,


//in the associated entity, so you can use the PHP constraint classes as validators
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image file' ,
                ])
            ],
        ])
         ->add('save', SubmitType::class, array('label'=> 'Update Todo', 'attr' => array('class'=> 'btn-primary', 'style'=>'margin-botton:15px')))
         ->getForm();
         $form->handleRequest($request);
 
 
        if($form->isSubmitted() && $form->isValid()){
            //fetching data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();
            $now = new \DateTime('now');
            $em = $this->getDoctrine()->getManager();
            $todo = $em->getRepository('App:Todo')->find($id);
            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($due_date);
            $todo->setCreateDate($now);
            
            $em->flush();
            $this->addFlash(
                    'notice',
                    'Todo Updated'
                    );
            return $this->redirectToRoute('todo');
        }
        return $this->render('todo/edit.html.twig', array('todo' => $todo, 'form' => $form->createView()));  
    }
 
 


    #[Route("/details/{id}", name:"todo_details")]

    public function details($id): Response
    {
        $todo = $this->getDoctrine()->getRepository('App:Todo')->find($id);
        return $this->render('todo/details.html.twig', array('todo' => $todo));
    }


    #[Route("/delete/{id}", name:"todo_delete")]
    public function delete($id){
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository('App:Todo')->find($id);
        $em->remove($todo);
  
        $em->flush();
        $this->addFlash(
            'notice',
            'Todo Removed'
        );
     
        return $this->redirectToRoute('todo');
    }

}

