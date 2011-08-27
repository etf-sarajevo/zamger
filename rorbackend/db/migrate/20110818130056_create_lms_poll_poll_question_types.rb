class CreateLmsPollPollQuestionTypes < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_question_types do |t|
      t.string :type, :limit => 32
      t.enum :choice_exists, :limit => ['Y', 'N']
      t.string :answers_table, :limit => 32

      # t.timestamps
    end
  end
end
