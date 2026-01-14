<template>
  <template>
    <ContactForm
      :buyer_id="buyer_id"
      :edit_contact="edit_contact">
    </ContactForm>
  </template>
  <v-data-table
    :style="theme_table_style"
    :headers="headers"
    :items="mapped_contacts"
    density="comfortable">
    <template #item.actions="{ item }">
      <v-btn
        text="Edit"
        size="small"
        color="primary"
        variant="outlined"
        prepend-icon="mdi-pencil"
        @click="EditContact(item)">
      </v-btn>
    </template>
    <template #footer.prepend>
      <v-btn
        class="ml-3"
        size="small"
        variant="flat"
        color="primary"
        @click="AddContact"
        prepend-icon="mdi-plus">
        Add Contact
      </v-btn>
      <v-spacer/>
    </template>
  </v-data-table>
</template>

<script setup>
import {GlobalStore} from "@/stores/globals";
import {theme_table_style} from "@/composables/GlobalComposables";
import {storeToRefs} from "pinia";

const edit_contact = ref(null);
const headers = [
  {title: 'Name',   value: 'name',sortable:true},
  {title: 'Title',  value: 'title',sortable:true},
  {title: 'Email',  value: 'email',sortable:true},
  {title: 'Phone',  value: 'phone',sortable:true},
  {title: 'Actions',value: 'actions', sortable: false},
];
const {modals} = storeToRefs(GlobalStore());
const mapped_contacts = computed(() => {
  return contacts.map(c => ({
    ...c,
    name: `${c.firstname} ${c.lastname}`,
  }))
});
const {contacts,buyer_id} = defineProps(['contacts','buyer_id']);

const EditContact = (item) => {
  const the_contact = {...item};
  delete the_contact.name;
  edit_contact.value = the_contact;
  modals.value.contact_form = true;
}
const AddContact = () => {
  edit_contact.value = null;
  modals.value.contact_form = true;
}
</script>
