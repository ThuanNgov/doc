{% block notice %}
    <div class="x_content">
    {% for message_level in ['success','error','info', 'warning'] %}
        {% set session_flashbag = app.session.flashbag.get(message_level) %}
            {% if session_flashbag %}
                {% for flash in session_flashbag %}
                    <div class="x_content bs-example-popovers">
                    <div class="alert {{ 'alert-' ~ message_level }} alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ flash|trans([], 'RwlAdminBundle') }}
                    </div>
                    </div>
                {% endfor %}
       {% endif %}
    {% endfor %}
    </div>
{% endblock notice%}

<a href="{{ path('rwl_admin_category_list') }}" class="btn btn-primary">{{'rwl.admin.btn.cancel'|trans({}, 'RwlAdminBundle')}}</a>

 {{ form_start(form,  {'attr':{novalidate: 'novalidate', 'class': 'form-horizontal form-label-left','data-parsley-validate': ""}}) }}

public function indexAction(Request $request)
{
    try {
        $form  = $this->createSearchForm();
        $form->handleRequest($request);
        $em    = $this->getDoctrine()->getManager();

        $paginator    = $this->get('knp_paginator');
        $listCategory = $em->getRepository('RwlCoreBundle:Category')->getCategories($request, $paginator, CoreConstants::PAGE_ROWS_LIMIT);

        return $this->render('RwlAdminBundle:Category:index.html.twig', array(
                'categories' => $listCategory,
                'form'       => $form->createView()
        ));
    } catch (Exception $ex) {
 
    }
}

    public function getCategories($request, $paginator, $limit = 10, $page_number = 1)
    {
        $sql = " SELECT a.id, a.name, a.email, a.phone, a.address"
            ." FROM RwlCoreBundle:Category a "
            ." WHERE a.name LIKE :name AND a.email LIKE :email AND a.phone LIKE :phone AND a.address LIKE :address "
            ." ORDER BY a.id DESC ";

        $query = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('name', '%'.$request->query->get('name').'%')
            ->setParameter('email', '%'.$request->query->get('email').'%')
            ->setParameter('phone', '%'.$request->query->get('phone').'%')
            ->setParameter('address', '%'.$request->query->get('address').'%');

        $pagination = $paginator->paginate(
            $query, $request->query->getInt('page', $page_number), $limit
        );

        return $pagination;
    }



public function editAction(Request $request, $id)
{
    try {
        $em       = $this->getDoctrine()->getManager();
        $category = $em->getRepository('RwlCoreBundle:Category')->find($id);

        if (!$category) {
            throw $this->createNotFoundException($this->get('translator')->trans('rwl.admin.category.error', array(), 'RwlAdminBundle'));
        }

        $form = $this->createForm(CategoryType::class, $category, array(
            'action' => $this->generateUrl('rwl_admin_category_update', array('id' => $id)),
            'method' => 'POST',
        ));

        return $this->render('RwlAdminBundle:Category:category.form.html.twig', array(
                'category' => $category,
                'form'     => $form->createView(),
                'mode'     => 'edit'
        ));
    } catch (Exception $ex) {
        throw $this->createNotFoundException($this->get('translator')->trans('rwl.admin.category.error', array(), 'RwlAdminBundle'));
    }
}


public function updateAction(Request $request, $id)
{


    $em       = $this->getDoctrine()->getManager();
    $category = $em->getRepository('RwlCoreBundle:Category')->find($id);

    if (!$category) {
            throw $this->createNotFoundException($this->get('translator')->trans('rwl.admin.category.error', array(), 'RwlAdminBundle'));
    }

    $form = $this->createForm(CategoryType::class, $category, array(
        'action' => $this->generateUrl('rwl_admin_category_update', array('id' => $id)),
        'method' => 'POST',
    ));
    $form->handleRequest($request);

    if ($form->isValid()) {


        if ($this->updateCategory($em, $category)) {
            $this->addFlash('success', $this->get('translator')->trans('rwl.admin.category.success', array(), 'RwlAdminBundle'));
        } else {
            $this->addFlash('error', $this->get('translator')->trans('rwl.admin.category.error', array(), 'RwlAdminBundle'));
        }

        return $this->redirect($this->generateUrl('rwl_admin_category_list'));
    }

    return $this->render('RwlAdminBundle:Category:category.form.html.twig', array(
            'category' => $category,
            'form'     => $form->createView()
    ));
}


    public function deleteAction(Request $request, $id)
    {
        try {
            $em       = $this->getDoctrine()->getManager();
            $category = $em->getRepository('RwlCoreBundle:Category')->find($id);

            if (!$category) {
                throw $this->createNotFoundException(
                    $this->get('translator')->trans('rwl.admin.category.error', array(), 'RwlAdminBundle'));
            }

      
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('rwl.admin.category.succes', array(), 'RwlAdminBundle'));

            return $this->redirect($this->generateUrl('Rwl_admin_category_list'));
        } catch (Exception $ex) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('rwl.admin.category.error', array(), 'RwlAdminBundle'));
        }
    }


{% extends 'RwlAdminBundle::layout.html.twig' %}
{% block body -%}
    <div class="">
        <div class="page-title">
            <div class="title_left">
                 <h3>{{'rwl.admin.category.title'|trans({}, 'RwlAdminBundle')}}</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        {% include 'RwlAdminBundle:Partials:show_flash.html.twig' %}
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                       <h2>{{'rwl.admin.category.search_title'|trans({}, 'RwlAdminBundle')}}</h2>
                       <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        {% include 'RwlAdminBundle:Category:searchForm.html.twig' %}
                    </div>
                </div>
            </div>
        </div>
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>{{'rwl.admin.category.list.title'|trans({}, 'RwlAdminBundle')}}</h2>
              <div class="btn-group navbar-right">
                <a class="btn btn-primary dropdown-toggle" href="{{ path('rwl_admin_category_create') }}"><i class="fa fa-plus"></i> {{'rwl.admin.category.btn.add'|trans({}, 'RwlAdminBundle')}}</a>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action">
                  <thead>
                    <tr>
                      <th style="width: 10%;">{{'rwl.admin.category.icon'|trans({}, 'RwlAdminBundle')}}</th>
                      <th>{{'rwl.admin.category.name'|trans({}, 'RwlAdminBundle')}}</th>
                      <th>{{'rwl.admin.category.parent_id'|trans({}, 'RwlAdminBundle')}}</th>
                      <th>{{'rwl.admin.category.status'|trans({}, 'RwlAdminBundle')}}</th>
                      <th style="width: 16%;">{{'rwl.admin.category.actions'|trans({}, 'RwlAdminBundle')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {% for entity in categories %}
                      <tr>
                        <td>
                          {% if entity.icon %}
                            <img src="{{ asset(entity.icon) }}" class="img-responsive" width="35px"/>
                          {% else %}
                            <img src="{{ asset('static/gentelella/production/images/images.png') }}" class="img-responsive" width="35px "/>
                          {% endif %}
                        </td>
                        <td>{{ entity.name }}</td>
                        <td class=" last">
                          {% if entity.parentId != null %}
                            <a href="{{ path('rwl_admin_category_edit', { 'id': entity.parentId }) }}" class="btn btn-info btn-xs">{{entity.parentName}}</a>
                          {% endif %}
                        </td>
                         <td>
                          {% if entity.status == 1 %}
                            <span class="label label-success">{{'admin.category.status.enable'|trans({}, 'RwlAdminBundle')}}</span>
                          {% else %}
                            <span class="label label-danger">{{'admin.category.status.disable'|trans({}, 'RwlAdminBundle')}}</span>
                          {% endif %}
                        </td>
                        <td class=" last">
                          <a href="{{ path('rwl_admin_category_edit', { 'id': entity.id }) }}" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> {{'rwl.admin.category.btn.edit'|trans({}, 'RwlAdminBundle')}} </a>
                          <a href="{{ path('rwl_admin_category_delete', { 'id': entity.id }) }}" class="btn btn-info btn-xs  btn-danger" onclick="return confirm('{{'rwl.admin.category.message.confirm.delete'|trans({}, 'RwlAdminBundle')}}');"><i class="fa fa-times"></i> {{'rwl.admin.category.btn.delete'|trans({}, 'RwlAdminBundle')}} </a>
                        </td>
                      </tr>
                    {% endfor %}
                  </tbody>
                </table>
              </div>
              <div class="wrap_pagination">
                {{ knp_pagination_render(categories) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!-- /page content -->
{% endblock %}


use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CatTreeType extends AbstractType
{
    private $catChoices;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $categories     = $em->getRepository('RwlCoreBundle:Category')->findParentId();
        $treeCategories = $this->generateTree($categories);
        $arrCategory    = $this->buildArrayFromTree($treeCategories);

        $catChoices = array();
        foreach ($arrCategory as $item) {
            $name                 = null;
            $name .= str_repeat('-', $item['level']);
            $name .= $item['name'];
            $catChoices[$name] = $item['id'];
        }

        $this->catChoices = $catChoices;
    }

    /**
     * Returns the configure options/class for this form.
     *
     * @param array $resolver
     *
     * @return array options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('choices' => $this->catChoices));
    }

    /**
     * Get parent
     *
     * @param array $resolver
     *
     * @return array options
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * Generate tree
     *
     * @param array $categories
     * @param integer $parentId
     * @param integer $level
     *
     * @return array $result
     */
    protected function generateTree(array &$categories, $parentId = 0, $level = 0)
    {

        $result = array();

        foreach ($categories as &$element) {

            if ($element['parentId'] == $parentId) {
                $children = $this->generateTree($categories, $element['id'], $level + 1);

                if ($children) {
                    $element['children'] = $children;
                }

                $element['level']       = $level;
                $result[$element['id']] = $element;
                unset($element);
            }
        }
        return $result;
    }

    /**
     * Build array from tree
     *
     * @param array $tree
     *
     * @return array $result
     */
    protected function buildArrayFromTree($tree)
    {

        $result = array();
        foreach ($tree as $item) {
            $temparr          = $item;
            $item['children'] = array();
            $result[]         = $item;

            if (!empty($temparr['children'])) {
                $childs = $this->buildArrayFromTree($temparr['children']);
                foreach ($childs as $child) {

                    if (!in_array($child['id'], $result)) {
                        $result[] = $child;
                    }
                }
                unset($item);
            }
        }

        return $result;
    }
}

//
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Rwl\CoreBundle\Entity\Category'
        );
    }

{% extends "@FOSUser/login_layout.html.twig" %}

{% block fos_user_content %}
    {{ include('@FOSUser/Security/login_content.html.twig') }}
{% endblock fos_user_content %}

/////
{% trans_default_domain 'FOSUserBundle' %}
  <div class="login_wrapper">
        <div class="animate form login_form">
            <section class="login_content">
              <form action="{{ path("fos_user_security_check") }}" method="post">
                {% if csrf_token %}
                    <input type="hidden" name="_csrf_token" value="{{ csrf_token }}" />
                {% endif %}
                <h1>{{'rwl.admin.login.title'|trans({}, 'RwlAdminBundle')}}</h1>
                {% if error %}
                  <ul class="parsley-errors-list filled" id="parsley-id-5"><li class="parsley-required">{{ error.messageKey|trans(error.messageData, 'security') }}</li></ul>
                {% endif %}
                <div>
                    <input type="text" class="form-control" placeholder="Username" id="username" name="_username" value="{{ last_username }}" required="required" />
                </div>
                <div>
                    <input type="password" class="form-control" placeholder="Password"  id="password" name="_password" required="required" />
                </div>
                <div>
                  <input style="margin: auto; float: none" type="submit" class="btn btn-default submit" id="_submit" name="_submit" value="{{ 'security.login.submit'|trans }}" />
                </div>
                  <div class="clearfix"></div>
                <div class="separator">
                  <div class="clearfix"></div>
                  <br />
                  <h1><i class="fa fa-flag-checkered"></i> {{'admin.login.name'|trans({}, 'RwlAdminBundle')}}</h1>
                  <p>{{'rwl.admin.login.footer'|trans({}, 'RwlAdminBundle')}}</p>
                </div>
              </form>
            </section>
        </div>
  </div>

