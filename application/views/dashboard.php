<div class="container">

<div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3 col-lg-8 col-lg-offset-2 ">
        <form action="" method="GET">
            <div id="notice-search-input">
                <div class="input-group col-md-12">
                    <input type="text" class="form-control input-sm" placeholder="Search Notices.." name="Search" />
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-sm" type="submit">
                            <i class="glyphicon glyphicon-search"></i>
                        </button>
                    </span>
                </div>
            </div>
           </form> 
           <div class="btn-group grid-view-button" role="group" aria-label="...">
           <a class="btn btn-default active" href="<?php echo base_url()?>student/dashboard/list" role="button"><i class="fa fa-th-list" aria-hidden="true"></i></a>
           <a class="btn btn-default" href="<?php echo base_url()?>student/dashboard/grid" role="button"><i class="fa fa-th-large" aria-hidden="true"></i></a>
            </div>
        </div>
	</div>

<div class="row">

  <div class="col-xs-12 col-md-6 col-md-offset-3 col-lg-8 col-lg-offset-2 ">
 

  <?php  foreach ($posts as $post) { ?>
    
    <div class="notice container" id="notice<?php echo $post['post_notice']->notice_Id; ?>"  >
    <?php
      $faculty = $post['post_notice']->faculty_Id;

      switch ($faculty) {
        case 1:
          echo '<div class="row title" style="background-color: #AAAAAA;">';
          break;
        case 2:
          echo '<div class="row title" style="background-color: #F25287;">';
          break;
        case 3:
          echo '<div class="row title" style="background-color: #A4EBF3;">';
          break;
        case 4:
          echo '<div class="row title" style="background-color: #FFDA77;">';
          break;
        case 5:
          echo '<div class="row title" style="background-color: #16C79A;">';
          break;
        default:
          echo '<div class="row title" style="background-color: #16A596;">';
      }
    ?> 
      
        <div class="col-xs-11 nopadding " ><p><?php echo $post['post_notice']->notice_type; ?></p></div>
        <div class="col-xs-1 nopadding" > <img class="pull-right pin" src="<?php echo base_url()?>assests/img/pin.png" ></div>
      </div>

      <div class="row content">
        <h4 class="content-title"><?php echo $post['post_notice']->title; ?></h4>

        <?php if(!empty($post['post_coverimage'])){ ?>
        <img class="content-img" src="<?php echo $post['post_coverimage']->cover_url; ?>" >
        <?php } ?>

        <div class="content-text">
        <?php echo $post['post_notice']->discription; ?> 
        </div>

        <?php if(!empty($post['post_links'])){ ?>
        <div class="content-attach">
          <p>Links:</p>
          <?php foreach ($post['post_links'] as $link) { ?>

            <a href="<?php  echo $link->link_url; ?>" target="_blank" > <?php  echo $link->link_name;  ?> </a>
            <br/>

           <?php } ?>
        </div>
        <?php } ?>

        <?php if(!empty($post['post_attachments'])){ ?>
        <div class="content-attach">
          <p>Attachments:</p>
          <?php foreach ($post['post_attachments'] as $attachment) { ?>

            <a href="<?php  echo $attachment->attachment_url; ?>" target="_blank" > <?php  echo $attachment->attachment_name;  ?> <i class="fa fa-download" aria-hidden="true"></i></a>
            <br/>

           <?php } ?>
        </div>
        <?php } ?>

        <div class="content-meta">
          <p> <i class="fa fa-user" aria-hidden="true"></i> <?php echo $post['post_author']->user_firstname.' '.$post['post_author']->user_lastname; ?> &nbsp; | &nbsp; <i class="fa fa-calendar-o" aria-hidden="true"></i> <?php echo $post['post_notice']->update_date; ?></p>
          <p> </p>
        </div>
    
      </div>

  </div>

   <?php } ?>



  </div>

  

</div>

</div>