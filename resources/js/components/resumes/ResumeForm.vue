<template>
  <div>
    <Alert
      v-if="
        (Array.isArray(alert.messages) && alert.messages.length > 0) ||
          typeof alert.messages === 'string'
      "
      :messages="alert.messages"
      :type="alert.type"
    />
    <div class="row mb-3">
      <div class="col-sm-8">
        <div class="form-group">
          <input
            v-model="resume.title"
            placeholder="Resume Title"
            required
            autofocus
            class="form-control w-100"
          />
        </div>
      </div>
      <div class="col-sm-4">
        <button class="btn btn-success btn-block" @click="submit()">
          Submit <i class="fa fa-upload"></i>
        </button>
      </div>
    </div>

    <Tabs>
      <Tab title="Basics" icon="fas fa-user">
        <VueFormGenerator
          :schema="schemas.basics"
          :model="resume.content.basics"
          :options="options"
        />
        <VueFormGenerator
          :schema="schemas.location"
          :model="resume.content.basics.location"
          :options="options"
        />
      </Tab>
      <Tab title="Profiles" icon="fa fa-users">
        <DynamicForm
          title="Profile"
          self="profiles"
          :model="resume.content.basics"
          :schema="schemas.profiles"
        />
      </Tab>
      <Tab title="Work" icon="fa fa-briefcase">
        <DynamicForm
          title="Work"
          self="work"
          :model="resume.content"
          :schema="schemas.work"
          :subforms="subforms.work"
        />
      </Tab>
      <Tab title="Education" icon="fa fa-graduation-cap">
        <DynamicForm
          title="Education"
          self="education"
          :model="resume.content"
          :schema="schemas.education"
          :subforms="subforms.education"
        />
      </Tab>
      <Tab title="Skills" icon="fa fa-lightbulb">
        <DynamicForm
          title="Skills"
          self="skills"
          :model="resume.content"
          :schema="schemas.skills"
          :subforms="subforms.skills"
        />
      </Tab>
      <Tab title="Awards" icon="fa fa-trophy">
        <DynamicForm
          title="Awards"
          self="awards"
          :model="resume.content"
          :schema="schemas.awards"
        />
      </Tab>
    </Tabs>
  </div>
</template>

<script>
import jsonresume from './jsonresume';
import Tabs from './tabs/Tabs';
import Tab from './tabs/Tab';
import basics from './schema/basics/basics';
import location from './schema/basics/location';
import profiles from './schema/basics/profiles';
import work from './schema/work';
import education from './schema/education';
import awards from './schema/awards';
import skills from './schema/skills';
import { component as VueFormGenerator } from 'vue-form-generator';
import 'vue-form-generator/dist/vfg.css';
import FieldResumeImage from './vfg/FieldResumeImage';
import DynamicForm from './dynamic/DynamicForm';
import ListForm from './dynamic/ListForm';
import Alert from '../reusable/Alert';
export default {
  name: 'ResumeForm',

  components: {
    Tabs,
    Tab,
    VueFormGenerator,
    FieldResumeImage,
    DynamicForm,
    Alert,
  },

  props: {
    update: false,
    resume: {
      type: Object,
      default: () => ({
        id: null,
        title: 'Resume Title',
        content: jsonresume,
      }),
    },
  },

  data() {
    return {
      alert: {
        type: 'warning',
        messages: [],
      },

      schemas: {
        basics,
        location,
        profiles,
        work,
        education,
        awards,
        skills,
      },

      subforms: {
        work: [
          {
            component: ListForm,
            props: {
              title: 'Highlights',
              self: 'highlights',
            },
          },
        ],
        education: [
          {
            component: ListForm,
            props: {
              title: 'Courses',
              self: 'courses',
              placeholder: 'DB101 - Basic SQL',
            },
          },
        ],
        skills: [
          {
            component: ListForm,
            props: {
              title: 'Keywords',
              self: 'keywords',
              placeholder: 'Javascript',
            },
          },
        ],
      },

      options: {
        validateAfterLoad: true,
        validateAfterChanged: true,
        validateAsync: true,
      },
    };
  },

  methods: {
    validate(target, parent = 'resume') {
      let errors = [];
      for (const [prop, value] of Object.entries(target)) {
        if (Array.isArray(value)) {
          if (value.length === 0) {
            errors.push(`${parent} > ${prop} must have al least one element`);
            continue;
          }
          for (const i in value) {
            if (typeof value[i] === null || value[i] === 'i') {
              errors.push(`${parent} > ${prop} > ${i} cannot be empty`);
            } else if (typeof value[i] === 'object') {
              errors = errors.concat(
                this.validate(value[i], `${parent} > ${prop} > ${i}`)
              );
            }
          }
        } else if (typeof value === 'object') {
          errors = errors.concat(this.validate(value, `${parent} > ${prop}`));
        } else if (value === null || value === '') {
          errors.push(`${parent} > ${prop} is required`);
        }
      }
      return errors;
    },

    isValid(){
      this.alert.messages = []
      const errors = this.validate(this.resume.content)
      if (errors.length < 1) {
        return true
      }

      this.alert.messages = errors.slice(0, 3)
      if (errors.length > 3) {
        this.alert.messages.push( `<strong>${errors.length - 3} more errors...</strong>` )
      }

      this.alert.type = 'warning'
      //this.alert.messages = errors
      return false
    },

    async submit() {
      if (!this.isValid()) {
        return;
      }
      try {
        const res = this.update
          ? await axios.put(route('resumes.update', this.resume.id),this.resume)
          : await axios.post(route('resumes.store'), this.resume);

        window.location = '/resumes';
        console.log(res.data);
      } catch (e) {
        this.alert.messages = [];
        this.alert.messages = ['Ha ocurrido un error'];
        //console.log(e.response.data.errors);
        const errors = e.response.data.errors;
        for (const [prop, values] of Object.entries(errors)) {
          let origin = prop.split('.');
          if (origin[0] === 'content') {
            origin.splice(0, 1);
          }
          origin = origin.join(' > ');
          for (const error of values) {
            const message = error.replace(prop, `<strong>${origin}</strong>`);
            this.alert.messages.push(message);
          }
        }
        this.alert.type = 'danger';
      }
    },
  },
};
</script>

<style></style>
